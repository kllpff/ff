<?php

return [
    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'database' => env('DB_DATABASE', 'ff_framework'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'port' => env('DB_PORT', 3306),
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_PATH', storage_path('database.sqlite')),
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', 'localhost'),
            'database' => env('DB_DATABASE', 'ff_framework'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
            'port' => env('DB_PORT', 5432),
        ],
    ],
];
