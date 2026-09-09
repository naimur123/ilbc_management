<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Order matters: roles/permissions and master data must exist before
     * the Microsoft catalog and demo data reference them. DemoDataSeeder
     * (sample vendors/customer/request + illustrative per-role logins with
     * password "password") is NOT run by default — it only runs here when
     * ILBC_SEED_DEMO_DATA=true is set in .env, so a plain
     * `php artisan db:seed` or `php artisan migrate --seed` from a
     * production deploy can never silently load fake data and throwaway
     * logins into a real database. The web installer's "Seed demo data"
     * checkbox achieves the same opt-in by calling seeders individually
     * (see InstallController::migrateRun) rather than through this class.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            MasterDataSeeder::class,
            MicrosoftProductCatalogSeeder::class,
        ]);

        if (config('ilbc.seed_demo_data')) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
