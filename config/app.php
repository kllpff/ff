<?php

$env = array_merge($_ENV, getenv());
$appUrl = $env['APP_URL'] ?? 'http://localhost';
$defaultHost = parse_url($appUrl, PHP_URL_HOST) ?: 'localhost';
$allowedHostsEnv = $env['APP_ALLOWED_HOSTS'] ?? '';
$allowedHosts = $allowedHostsEnv !== ''
    ? array_values(array_unique(array_filter(array_map('trim', explode(',', $allowedHostsEnv)))))
    : [$defaultHost];

// Parse trusted proxies from environment
$trustedProxiesEnv = $env['TRUSTED_PROXIES'] ?? '';
$trustedProxies = $trustedProxiesEnv !== ''
    ? array_values(array_unique(array_filter(array_map('trim', explode(',', $trustedProxiesEnv)))))
    : [];

// Special value '*' means trust all proxies (use with caution!)
if ($trustedProxiesEnv === '*') {
    $trustedProxies = '*';
}

return [
    'name' => env('APP_NAME', 'FF Framework'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'allowed_hosts' => $allowedHosts,
    'trusted_proxies' => $trustedProxies,
    'locale' => env('APP_LOCALE', 'en'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'key' => env('APP_KEY', ''),
    'cipher' => env('APP_CIPHER', 'AES-256-CBC'),
    
    // Security Headers
    'enable_csp' => env('ENABLE_CSP', true), // Отключить CSP для development если нужно
];
