<?php

return [

    /*
    |--------------------------------------------------------------------
    | Global vendor API automation switch
    |--------------------------------------------------------------------
    |
    | Mirrors system_settings key `enable_vendor_api_automation` so it can
    | also be overridden per-environment via .env. Off by default: with
    | this false, every AbstractApiProvisioningProvider call throws
    | ProvisioningNotConfiguredException regardless of per-vendor setup,
    | which keeps Loading fully manual until a pilot vendor is deliberately
    | switched on.
    |
    */
    'enabled' => env('VENDOR_PROVISIONING_ENABLED', false),

    /*
    |--------------------------------------------------------------------
    | Known providers
    |--------------------------------------------------------------------
    |
    | Base URLs are the real production endpoints; sandbox/test base URLs
    | should be stored per-vendor in vendor_provisioning_accounts instead,
    | since Partner Center/Crayon sandbox access is credential-specific.
    |
    */
    'providers' => [
        'manual' => [
            'label' => 'Manual (no API)',
        ],
        'partner_center' => [
            'label' => 'Microsoft Partner Center',
            'base_url' => env('PARTNER_CENTER_BASE_URL', 'https://api.partnercenter.microsoft.com'),
            'token_url' => env('PARTNER_CENTER_TOKEN_URL', 'https://login.microsoftonline.com/organizations/oauth2/v2.0/token'),
        ],
        'crayon' => [
            'label' => 'Crayon CloudIQ',
            'base_url' => env('CRAYON_BASE_URL', 'https://api.crayon.com'),
        ],
    ],

    /*
    |--------------------------------------------------------------------
    | Queue connection used for provisioning jobs
    |--------------------------------------------------------------------
    */
    'queue' => env('VENDOR_PROVISIONING_QUEUE', 'provisioning'),

    /*
    |--------------------------------------------------------------------
    | Webhook signature secrets (per provider), read at
    | POST /webhooks/provisioning/{provider}
    |--------------------------------------------------------------------
    */
    'webhook_secrets' => [
        'partner_center' => env('PARTNER_CENTER_WEBHOOK_SECRET'),
        'crayon' => env('CRAYON_WEBHOOK_SECRET'),
    ],
];
