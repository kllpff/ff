<?php

$env = array_merge($_ENV, getenv());
$appUrl = $env['APP_URL'] ?? 'http://localhost';
$defaultHost = parse_url($appUrl, PHP_URL_HOST) ?: 'localhost';
$allowedHostsEnv = $env['APP_ALLOWED_HOSTS'] ?? '';
$allowedHosts = $allowedHostsEnv !== ''
    ? array_values(array_unique(array_filter(array_map('trim', explode(',', $allowedHostsEnv)))))
    : [$defaultHost];

return [
    'name' => env('APP_NAME', 'FF Framework'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'allowed_hosts' => $allowedHosts,
    'locale' => env('APP_LOCALE', 'en'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'key' => env('APP_KEY', ''),
    'cipher' => env('APP_CIPHER', 'AES-256-CBC'),
];
