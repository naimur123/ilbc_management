<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

/**
 * No-SSH installer (Section 46's cPanel constraint: File Manager/FTP only,
 * no terminal/Composer access on the server). Every step here is something
 * an `artisan` command would normally do, exposed instead as a guarded web
 * flow: write .env, run migrations, run seeders, create the lock file.
 *
 * Locked permanently once storage/installed.lock exists — InstallGuard
 * middleware (see bootstrap/app.php) redirects away from every /install/*
 * route after that, so this can never be re-run by an attacker who finds
 * the URL after go-live.
 */
class InstallController extends Controller
{
    private string $lockFile;

    public function __construct()
    {
        $this->lockFile = storage_path('installed.lock');
    }

    public function welcome()
    {
        $checks = [
            'PHP >= 8.2' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'PDO MySQL extension' => extension_loaded('pdo_mysql'),
            'OpenSSL extension' => extension_loaded('openssl'),
            'Mbstring extension' => extension_loaded('mbstring'),
            'Fileinfo extension' => extension_loaded('fileinfo'),
            '.env is writable (or creatable)' => $this->envWritable(),
            'storage/ is writable' => is_writable(storage_path()),
            'bootstrap/cache/ is writable' => is_writable(base_path('bootstrap/cache')),
            'vendor/ exists (composer install was run)' => is_dir(base_path('vendor')),
        ];

        $allOk = ! in_array(false, $checks, true);

        return view('install.welcome', compact('checks', 'allOk'));
    }

    public function databaseForm()
    {
        return view('install.database', ['env' => $this->currentEnv()]);
    }

    public function databaseSave(Request $request)
    {
        $data = $request->validate([
            'db_host' => 'required|string|max:150',
            'db_port' => 'required|numeric',
            'db_database' => 'required|string|max:100',
            'db_username' => 'required|string|max:100',
            'db_password' => 'nullable|string|max:200',
            'app_url' => 'required|url',
        ]);

        // Prove the credentials work before writing anything, so a typo
        // never leaves the app half-configured.
        try {
            $pdo = new \PDO(
                "mysql:host={$data['db_host']};port={$data['db_port']};dbname={$data['db_database']}",
                $data['db_username'],
                $data['db_password'] ?? ''
            );
            $pdo = null;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Could not connect to that database: '.$e->getMessage());
        }

        $this->writeEnv([
            'APP_URL' => $data['app_url'],
            'APP_KEY' => config('app.key') ?: 'base64:'.base64_encode(random_bytes(32)),
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $data['db_host'],
            'DB_PORT' => $data['db_port'],
            'DB_DATABASE' => $data['db_database'],
            'DB_USERNAME' => $data['db_username'],
            'DB_PASSWORD' => $data['db_password'] ?? '',
        ]);

        Artisan::call('config:clear');

        return redirect()->route('install.migrate');
    }

    public function migrateForm()
    {
        return view('install.migrate');
    }

    public function migrateRun(Request $request)
    {
        try {
            Artisan::call('migrate', ['--force' => true]);

            // Roles/permissions, the workflow definition, SLA/approval
            // rules and the product catalog are structural data the app
            // cannot run without — always seeded, regardless of the demo
            // checkbox. Only DemoDataSeeder (sample vendors/customer/
            // request + illustrative per-role logins) is optional.
            Artisan::call('db:seed', ['--class' => \Database\Seeders\RolesAndPermissionsSeeder::class, '--force' => true]);
            Artisan::call('db:seed', ['--class' => \Database\Seeders\MasterDataSeeder::class, '--force' => true]);
            Artisan::call('db:seed', ['--class' => \Database\Seeders\MicrosoftProductCatalogSeeder::class, '--force' => true]);

            if ($request->boolean('seed')) {
                Artisan::call('db:seed', ['--class' => \Database\Seeders\DemoDataSeeder::class, '--force' => true]);
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Migration failed: '.$e->getMessage());
        }

        File::ensureDirectoryExists(storage_path('app/public'));
        $this->linkStorage();

        return redirect()->route('install.admin');
    }

    public function adminForm()
    {
        return view('install.admin');
    }

    public function adminSave(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = \App\Models\User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
            'is_active' => true,
        ]);
        $user->assignRole('Super Admin');

        File::put($this->lockFile, 'Installed at '.now()->toDateTimeString());

        return redirect()->route('install.done');
    }

    public function done()
    {
        return view('install.done');
    }

    private function envWritable(): bool
    {
        $path = base_path('.env');

        return file_exists($path) ? is_writable($path) : is_writable(base_path());
    }

    private function currentEnv(): array
    {
        $path = base_path('.env');
        $values = [];

        if (file_exists($path)) {
            foreach (file($path) as $line) {
                if (str_contains($line, '=') && ! str_starts_with(trim($line), '#')) {
                    [$key, $value] = explode('=', $line, 2);
                    $values[trim($key)] = trim($value);
                }
            }
        }

        return $values;
    }

    private function writeEnv(array $values): void
    {
        $path = base_path('.env');
        $env = $this->currentEnv();
        $env = array_merge($env, $values);

        $lines = [];
        foreach ($env as $key => $value) {
            $needsQuotes = $value === '' || preg_match('/\s/', (string) $value);
            $lines[] = $key.'='.($needsQuotes ? '"'.$value.'"' : $value);
        }

        File::put($path, implode("\n", $lines)."\n");
    }

    /**
     * PHP-native replacement for `artisan storage:link` — a cPanel/FTP-only
     * host has no CLI, so this uses symlink() directly (falls back to a
     * recursive copy if the host disables symlink(), which some shared
     * hosts do).
     */
    private function linkStorage(): void
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (file_exists($link)) {
            return;
        }

        if (function_exists('symlink')) {
            try {
                symlink($target, $link);

                return;
            } catch (\Throwable $e) {
                // fall through to copy
            }
        }

        File::ensureDirectoryExists($link);
        File::copyDirectory($target, $link);
    }
}
