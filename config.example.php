<?php
// Salin file ini menjadi config.php lalu isi kredensial Anda.
// Jangan unggah config.php ke repositori publik.
return [
    'app_url' => 'http://localhost:8000',
    'session_name' => 'nusantara_auth',
    'db' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'auth_website',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'oauth' => [
        'google' => [
            'client_id' => '',
            'client_secret' => '',
            'redirect_uri' => 'http://localhost:8000/oauth-callback.php?provider=google',
        ],
        'facebook' => [
            'client_id' => '',
            'client_secret' => '',
            'redirect_uri' => 'http://localhost:8000/oauth-callback.php?provider=facebook',
        ],
    ],
    'twilio' => [
        'account_sid' => '',
        'auth_token' => '',
        'verify_service_sid' => '',
    ],
];