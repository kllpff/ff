<?php

namespace FF\Debug;

use FF\Core\Application;
use FF\Http\Response;
use FF\Log\Logger;
use Throwable;

/**
 * ExceptionHandler - Exception Handler
 * 
 * Handles exceptions and generates appropriate error responses
 * for development and production environments.
 */
class ExceptionHandler
{
    /**
     * The application instance
     * 
     * @var Application
     */
    protected Application $app;

    /**
     * Create a new ExceptionHandler instance
     * 
     * @param Application $app The application instance
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle an exception and return an error response
     * 
     * @param Throwable $e The exception
     * @return Response The error response
     */
    public function handle(Throwable $e): Response
    {
        // Log the exception
        $this->log($e);

        // Render appropriate error page
        if ($this->app->isDebugMode()) {
            return $this->renderForDevelopment($e);
        } else {
            return $this->renderForProduction($e);
        }
    }

    /**
     * Log the exception
     * 
     * @param Throwable $e The exception
     * @return void
     */
    protected function log(Throwable $e): void
    {
        // Centralized structured logging
        $message = sprintf(
            '%s: %s in %s:%d',
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );

        $context = [
            'code' => $e->getCode(),
            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'uri' => $_SERVER['REQUEST_URI'] ?? null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_id' => function_exists('session') ? (session()->get('auth_user_id') ?? null) : null,
            'request_id' => function_exists('request_id') ? request_id() : null,
        ];

        // Use application logger singleton
        /** @var Logger $logger */
        $logger = $this->app->make(Logger::class);
        $logger->error($message, $context);
    }

    /**
     * Render exception for production environment
     * 
     * @param Throwable $e The exception
     * @return Response
     */
    protected function renderForProduction(Throwable $e): Response
    {
        $statusCode = 500;
        $title = 'Server Error';
        $message = 'An error occurred while processing your request.';

        // Special handling for 404 errors
        if (strpos($e->getMessage(), 'No routes matched') !== false) {
            $statusCode = 404;
            $title = 'Not Found';
            $message = 'The requested resource was not found.';
        }

        $html = $this->renderErrorPage($title, $message, $statusCode);

        return new Response($html, $statusCode, ['Content-Type' => 'text/html']);
    }

    /**
     * Render exception for development environment
     * 
     * @param Throwable $e The exception
     * @return Response
     */
    protected function renderForDevelopment(Throwable $e): Response
    {
        $statusCode = 500;
        $className = get_class($e);
        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();
        $trace = $e->getTraceAsString();
        $phpVersion = $this->phpVersion();
        $environment = $this->app->getEnvironment();
        $timestamp = date('Y-m-d H:i:s');

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$className</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #1e1e1e; color: #e0e0e0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        .header { background: #d32f2f; color: white; padding: 30px; margin-bottom: 30px; border-radius: 5px; }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .header p { font-size: 14px; opacity: 0.9; }
        .section { background: #2d2d2d; padding: 20px; margin-bottom: 20px; border-radius: 5px; border-left: 4px solid #d32f2f; }
        .section-title { font-size: 16px; font-weight: bold; margin-bottom: 10px; color: #64b5f6; }
        .section-content { font-size: 14px; line-height: 1.6; font-family: 'Courier New', monospace; overflow-x: auto; }
        code { background: #1a1a1a; padding: 2px 4px; border-radius: 3px; }
        .trace { background: #1a1a1a; padding: 15px; border-radius: 3px; font-size: 12px; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>$className</h1>
            <p>$message</p>
        </div>

        <div class="section">
            <div class="section-title">📍 Location</div>
            <div class="section-content">
                <strong>File:</strong> $file<br>
                <strong>Line:</strong> $line
            </div>
        </div>

        <div class="section">
            <div class="section-title">📋 Stack Trace</div>
            <div class="trace">
                <pre>$trace</pre>
            </div>
        </div>

        <div class="section">
            <div class="section-title">ℹ️ Debug Information</div>
            <div class="section-content">
                <strong>PHP Version:</strong> $phpVersion<br>
                <strong>Environment:</strong> $environment<br>
                <strong>Time:</strong> $timestamp
            </div>
        </div>
    </div>
</body>
</html>
HTML;

        return new Response($html, $statusCode, ['Content-Type' => 'text/html']);
    }

    /**
     * Render a simple error page
     * 
     * @param string $title Error title
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @return string HTML
     */
    protected function renderErrorPage(string $title, string $message, int $statusCode): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$statusCode - $title</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; color: #333; }
        .container { display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .content { text-align: center; background: white; padding: 60px 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { font-size: 72px; color: #d32f2f; margin-bottom: 20px; }
        h2 { font-size: 28px; color: #333; margin-bottom: 15px; }
        p { font-size: 16px; color: #666; margin-bottom: 30px; }
        a { color: #1976d2; text-decoration: none; font-size: 16px; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <h1>$statusCode</h1>
            <h2>$title</h2>
            <p>$message</p>
            <a href="/">Go to Home Page</a>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Get PHP version
     * 
     * @return string
     */
    protected function phpVersion(): string
    {
        return phpversion();
    }
}
