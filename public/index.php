<?php

/**
 * FF Framework - Entry Point
 * 
 * This is the main entry point for all HTTP requests.
 * All requests are routed through this single file (front controller pattern).
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Create the application
$app = new FF\Framework\Core\Application(BASE_PATH);

// Make it available globally
$GLOBALS['application'] = $app;

// Bootstrap the application
$app->bootstrap([
    // Service providers would be registered here in production
    // App\Providers\AppServiceProvider::class,
    // App\Providers\RouterServiceProvider::class,
]);

// Create the HTTP Kernel
$kernel = new FF\Framework\Core\Kernel($app);

// Capture the current request
$request = FF\Framework\Http\Request::capture();

// Handle the request
$response = $kernel->handle($request);

// Send the response
$response->send();
