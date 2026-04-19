<?php

return [

    'secret' => env('JWT_SECRET'),

    'keys' => [
        'public'     => env('JWT_PUBLIC_KEY'),
        'private'    => env('JWT_PRIVATE_KEY'),
        'passphrase' => env('JWT_PASSPHRASE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Access token: 1 día en desarrollo, 60 min en producción
    |--------------------------------------------------------------------------
    */
    'ttl' => (int) env('JWT_TTL', 1440),

    /*
    |--------------------------------------------------------------------------
    | Refresh token: 14 días
    | refresh_iat: false = la ventana de 14 días se cuenta desde el LOGIN
    |              true  = la ventana se renueva en cada refresh (rolling)
    | Para un sistema normal usa false — más seguro
    |--------------------------------------------------------------------------
    */
    'refresh_iat' => env('JWT_REFRESH_IAT', false),
    'refresh_ttl' => (int) env('JWT_REFRESH_TTL', 20160),

    'algo' => env('JWT_ALGO', 'HS256'),

    'required_claims' => [
        'iss',
        'iat',
        'exp',
        'nbf',
        'sub',
        'jti',
    ],

    /*
    |--------------------------------------------------------------------------
    | Persistent claims: datos que se mantienen al hacer refresh
    | Agrega aquí los custom claims que quieres que persistan
    |--------------------------------------------------------------------------
    */
    'persistent_claims' => [
        'email',
        'name',
        'last_name',
        'role',
        'role_id',
        'status',
    ],

    'lock_subject' => true,

    /*
    |--------------------------------------------------------------------------
    | Leeway: margen de segundos para diferencias de reloj entre servidores
    | En desarrollo: 0
    | En producción con múltiples servidores: 30-60 segundos
    |--------------------------------------------------------------------------
    */
    'leeway' => (int) env('JWT_LEEWAY', 0),

    /*
    |--------------------------------------------------------------------------
    | Blacklist: DEBE estar en true para que logout funcione
    | Sin esto, el token sigue válido aunque el usuario haga logout
    |--------------------------------------------------------------------------
    */
    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Grace period: útil cuando hay múltiples requests simultáneos
    | Evita que requests paralelos fallen al renovar el token
    | Pon 10-30 segundos si tu app hace muchos requests al mismo tiempo
    |--------------------------------------------------------------------------
    */
    'blacklist_grace_period' => (int) env('JWT_BLACKLIST_GRACE_PERIOD', 10),

    'show_black_list_exception' => env('JWT_SHOW_BLACKLIST_EXCEPTION', false),

    'decrypt_cookies' => false,

    'cookie_key_name' => 'token',

    'providers' => [
        'jwt'     => PHPOpenSourceSaver\JWTAuth\Providers\JWT\Lcobucci::class,
        'auth'    => PHPOpenSourceSaver\JWTAuth\Providers\Auth\Illuminate::class,
        'storage' => PHPOpenSourceSaver\JWTAuth\Providers\Storage\Illuminate::class,
    ],
];
