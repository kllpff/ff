<?php

/**
 * FF Framework - Entry Point
 * 
 * This is the main entry point for all HTTP requests.
 * All requests are routed through this single file (front controller pattern).
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Load environment variables (optional in production)
$dotenvPath = BASE_PATH . '/.env';
if (file_exists($dotenvPath)) {
    FF\Support\DotEnv::createImmutable(BASE_PATH)->safeLoad();
}

$debug = filter_var($_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?? false, FILTER_VALIDATE_BOOLEAN);

// Configure error reporting based on environment
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');

// Create the application
$app = new FF\Core\Application(BASE_PATH);

// Make it available globally
$GLOBALS['application'] = $app;

// Bootstrap the application
$app->bootstrap([
    // Service providers would be registered here in production
    // App\Providers\AppServiceProvider::class,
    // App\Providers\RouterServiceProvider::class,
]);

// Start session BEFORE any middleware
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_domain', ''); // Empty = current domain
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_secure', '0'); // 0 = works with HTTP
    ini_set('session.use_strict_mode', '1');

    session_start();
}

// Create the HTTP Kernel
$kernel = new FF\Core\Kernel($app);
$kernel->registerMiddleware([
    \FF\Http\Middleware\CorrelationIdMiddleware::class,
    \FF\Http\Middleware\RequestSizeLimitMiddleware::class,
    \FF\Http\Middleware\SecurityHeadersMiddleware::class,
    \FF\Http\Middleware\CsrfMiddleware::class,
]);

// Capture the current request
$request = FF\Http\Request::capture();

// Handle the request
$response = $kernel->handle($request);

// Send the response
$response->send();
