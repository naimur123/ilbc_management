<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Auto-provision .env before Laravel boots (Section 46 no-SSH installer)
|--------------------------------------------------------------------------
| A fresh cPanel upload (File Manager/FTP only, per Section 1's hosting
| constraint) has no .env file at all — the README tells the user they
| never have to create one by hand, the web installer at /install does it.
| But every request, including the very first hit to /install/welcome,
| already passes through Laravel's default "web" middleware group, which
| includes EncryptCookies. That middleware needs a real APP_KEY to encrypt
| the session cookie, and without a .env file config/session.php also
| falls back to its own default SESSION_DRIVER of "database" — a table
| that doesn't exist until the installer's migrate step runs. Either gap
| crashes the welcome screen before the requirements checklist can even
| render. Both are plain file/PHP-config problems, not application logic,
| so they're resolved here — before the framework, its config cache, and
| its middleware stack are even loaded — rather than deeper inside a
| controller or middleware that only runs after the crash has already
| happened.
*/
$envPath = __DIR__.'/../.env';

if (! file_exists($envPath)) {
    $examplePath = __DIR__.'/../.env.example';

    if (file_exists($examplePath)) {
        copy($examplePath, $envPath);
    }
}

if (file_exists($envPath)) {
    $envContents = file_get_contents($envPath);

    $hasBlankKey = (bool) preg_match('/^APP_KEY=\s*$/m', $envContents)
        || ! preg_match('/^APP_KEY=/m', $envContents);

    if ($hasBlankKey) {
        $generatedKey = 'base64:'.base64_encode(random_bytes(32));

        if (preg_match('/^APP_KEY=\s*$/m', $envContents)) {
            $envContents = preg_replace('/^APP_KEY=\s*$/m', 'APP_KEY='.$generatedKey, $envContents, 1);
        } else {
            $envContents = "APP_KEY={$generatedKey}\n".$envContents;
        }

        file_put_contents($envPath, $envContents);
    }
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
