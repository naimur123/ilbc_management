<?php

/**
 * Section 1's default configuration, kept out of code so it's editable
 * from System Settings (which writes into the system_settings table —
 * SettingsController falls back to these config values as the shipped
 * defaults before any row exists).
 */
return [
    'currency_code' => env('ILBC_CURRENCY_CODE', 'BDT'),
    'currency_symbol' => env('ILBC_CURRENCY_SYMBOL', '৳'),
    'timezone' => env('ILBC_TIMEZONE', 'Asia/Dhaka'),
    'date_format' => env('ILBC_DATE_FORMAT', 'd-m-Y'),      // DD-MM-YYYY
    'month_format' => env('ILBC_MONTH_FORMAT', 'M-Y'),      // MMM-YYYY
    'company_name' => env('ILBC_COMPANY_NAME', 'Your Company Name'),

    // Section 17/36: default conditional-approval thresholds. Real values
    // live in approval_rules (seeded from these), so changing them later
    // is done from Settings > Financial Settings, not by editing this file.
    'minimum_margin_percent' => env('ILBC_MIN_MARGIN_PERCENT', 10),
    'department_head_approval_amount' => env('ILBC_DEPT_HEAD_APPROVAL_AMOUNT', 500000),

    // Section 46: DemoDataSeeder (sample vendors/customer/request +
    // illustrative per-role logins with password "password") must never
    // load into a real production database by accident. The web installer
    // (InstallController::migrateRun) already makes this an explicit
    // checkbox by calling seeders individually instead of DatabaseSeeder —
    // this flag gives the same opt-in safety to `php artisan db:seed` /
    // `php artisan migrate --seed` run directly from the command line.
    // Defaults to false; set ILBC_SEED_DEMO_DATA=true in .env before
    // seeding if you want the sample data loaded that way.
    'seed_demo_data' => env('ILBC_SEED_DEMO_DATA', false),
];
