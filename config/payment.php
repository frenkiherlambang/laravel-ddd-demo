<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Driver Payment Gateway
    |--------------------------------------------------------------------------
    |
    | 'fake' -> FakeDokuGateway (default, jalan offline, dipakai demo & test).
    | 'doku' -> DokuHttpGateway (integrasi nyata; butuh kredensial di bawah).
    |
    */
    'driver' => env('PAYMENT_DRIVER', 'fake'),

    /*
    |--------------------------------------------------------------------------
    | Fake Gateway
    |--------------------------------------------------------------------------
    |
    | Berapa kali polling sebelum transaksi simulasi dianggap lunas.
    |
    */
    'fake' => [
        'polls_until_paid' => (int) env('PAYMENT_FAKE_POLLS_UNTIL_PAID', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | DOKU (Jokul) Credentials
    |--------------------------------------------------------------------------
    */
    'doku' => [
        'base_url' => env('DOKU_BASE_URL', 'https://api-sandbox.doku.com'),
        'client_id' => env('DOKU_CLIENT_ID', ''),
        'secret_key' => env('DOKU_SECRET_KEY', ''),
    ],

];
