<?php

return [
    'services' => [
        'user' => [
            'url'             => env('USER_SERVICE_URL', 'http://jd_user_app:80'),
            'timeout'         => env('USER_SERVICE_TIMEOUT', 30),
            'connect_timeout' => 3,
            'prefix'          => 'api',
        ],
        'catalog' => [
            'url'             => env('CATALOG_SERVICE_URL', 'http://jd_catalog_app:80'),
            'timeout'         => env('CATALOG_SERVICE_TIMEOUT', 30),
            'connect_timeout' => 3,
            'prefix'          => 'api',
        ],
    ],

    'rate_limit' => [
        'max_requests'  => env('GATEWAY_RATE_LIMIT', 60),
        'decay_seconds' => 60,
    ],

    'cors' => [
        'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),
        'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
    ],

];