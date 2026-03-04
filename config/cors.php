<?php

return [
    'paths' => ['api/*', 'storage/*', 'sanctum/csrf-cookie', '*'],

    'allowed_methods' => ['*'],

    // Add your local IP for mobile testing
    'allowed_origins' => [
        'http://localhost',
        'http://127.0.0.1:8000',
        'http://10.0.2.2:8000', // Android emulator
        'http://localhost:8000',
        // Add your computer's IP for physical device testing
        'http://192.168.1.*', // Your local network IP range
    ],

    'allowed_origins_patterns' => [
        '/^http:\/\/192\.168\.\d+\.\d+(:\d+)?$/', // Any local IP
        '/^http:\/\/10\.0\.2\.\d+(:\d+)?$/', // Android emulator range
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // Change to true
];
