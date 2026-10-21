<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Payment Gateway
    |--------------------------------------------------------------------------
    | Konfigurasi koneksi ke Midtrans (mode Sandbox / Production).
    | Ambil kredensial dari dashboard.midtrans.com → Settings → Access Keys.
    */

    'server_key' => env('MIDTRANS_SERVER_KEY', ''),

    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),

    // true = production, false = sandbox. Amankan ini lewat .env.
    'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),

    // Sanitasi & 3DS hanya berlaku untuk transaksi kartu kredit.
    'is_sanitized' => (bool) env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds' => (bool) env('MIDTRANS_IS_3DS', true),

    // URL notifikasi/webhook yang dipanggil Midtrans. Default: /midtrans/notifikasi
    'notification_url' => env('MIDTRANS_NOTIFICATION_URL'),

    /*
    |--------------------------------------------------------------------------
    | Endpoint API
    |--------------------------------------------------------------------------
    */
    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/v1/transactions'
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions',

    'snap_js_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',

    'api_status_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://api.midtrans.com/v2/'
        : 'https://api.sandbox.midtrans.com/v2/',

    // Opsi konfigurasi Snap (channel yang diaktifkan di sisi Midtrans)
    'enabled_payments' => array_filter(explode(',', (string) env('MIDTRANS_ENABLED_PAYMENTS', ''))),

    // Waktu kedaluwarsa transaksi (menit)
    'expiry_minutes' => (int) env('MIDTRANS_EXPIRY_MINUTES', 1440),
];
