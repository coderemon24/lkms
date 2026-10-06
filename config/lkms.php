<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LKMS Licensing Authority — Central Connection Configuration
    |--------------------------------------------------------------------------
    | Pre-configured & embedded parameters. No .env dependency required.
    */

    // Central LKMS Server API Endpoint
    'server_url' => env('LKMS_SERVER_URL', 'http://lkms.test/api/v1'),

    // API Credentials for Central Authorization
    'client_id' => 'lkms_cid_demo_test_client_12345',
    'client_secret' => 'lkms_sec_demo_secret_key_8899aabb',

    // Embedded RSA-2048 Public Verification Key (Private key NEVER leaves server)
    'public_key' => <<<PEM
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtXhmCfX9ktjGoIkjyFsi
PQ7jFTT0S9M/hrUN/aK85xysAwxXZC+Wyib3gBwy/cWCxtlLPqSyLCG/oAyRILLe
0nDA4GywUboaPkL09F1kZ+PKwTBgCo2/IKPveh549JNLRvIZS+MVREBdL/HM5dVt
949L0frdRuwbreB1IuQVuCpD3zreILYC+ofiCKbEy9AzVBtQh0dln+QPtsxFBhn2
5+44denHo91ezpYzhvQfbZrUoIlgvseZEvDMLIxCHHXmbvyEGiMymGqziQgy3e+4
2RlVFRyLHbd7zMUPAMIE05ndcv3WMjP1LUGcWj9WAu2KXf/BAknX3XhJNF6Nu7Le
MwIDAQAB
-----END PUBLIC KEY-----
PEM,

    // Offline Grace Period (Days allowed if central server is offline)
    'grace_period_days' => 7,

    // Heartbeat Sync Interval (Hours)
    'heartbeat_hours' => 24,

    // Route Configuration
    'routes' => [
        'prefix' => 'license',
        'middleware' => ['web'],
    ],

    // Stealth Mode: Automatically inject license enforcement into the 'web' group
    // No manual registration required in bootstrap/app.php
    'auto_enforce' => env('LKMS_AUTO_ENFORCE', false),

    // Redirect Target upon Successful Activation
    'redirect_after_activation' => '/',
];
