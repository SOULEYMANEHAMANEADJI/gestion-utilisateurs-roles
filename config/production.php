<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration de Production
    |--------------------------------------------------------------------------
    |
    | Configuration spécifique à l'environnement de production
    |
    */

    'debug' => false,
    'app_env' => 'production',
    'app_debug' => false,
    
    /*
    |--------------------------------------------------------------------------
    | Sécurité
    |--------------------------------------------------------------------------
    */
    'security' => [
        'force_https' => true,
        'secure_cookies' => true,
        'session_secure' => true,
        'session_httponly' => true,
        'session_same_site' => 'strict',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'driver' => 'redis',
        'ttl' => 3600, // 1 heure
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Logs
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'level' => 'error',
        'channels' => [
            'single' => [
                'driver' => 'single',
                'path' => storage_path('logs/laravel.log'),
                'level' => 'error',
            ],
            'daily' => [
                'driver' => 'daily',
                'path' => storage_path('logs/laravel.log'),
                'level' => 'error',
                'days' => 14,
            ],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Performance
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'optimize_autoloader' => true,
        'cache_config' => true,
        'cache_routes' => true,
        'cache_views' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limiting' => [
        'api' => '60,1', // 60 requêtes par minute
        'login' => '5,1', // 5 tentatives par minute
        'admin' => '100,1', // 100 requêtes par minute
        'bulk_action' => '10,1', // 10 actions par minute
    ],
];
