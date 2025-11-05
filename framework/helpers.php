<?php

/**
 * Framework Helper Functions
 * 
 * Global helper functions for common tasks
 */

if (!function_exists('app')) {
    /**
     * Get the application instance
     */
    function app($service = null)
    {
        $app = $GLOBALS['application'] ?? null;

        if (!$app) {
            throw new \Exception('Application instance not available');
        }

        if ($service === null) {
            return $app;
        }

        return $app->make($service);
    }
}

if (!function_exists('view')) {
    /**
     * Render a view
     * 
     * @param string $name View name (e.g., 'home' or 'blog.index')
     * @param array $data Data to pass to view
     * @return string Rendered HTML
     */
    function view(string $name, array $data = []): string
    {
        $basePath = BASE_PATH . '/app/Views';
        $viewPath = $basePath . '/' . str_replace('.', '/', $name) . '.php';

        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: $name");
        }

        // Extract data into variables
        extract($data, EXTR_SKIP);

        // Load app helpers if available
        $appHelpersPath = BASE_PATH . '/app/helpers.php';
        if (file_exists($appHelpersPath)) {
            require_once $appHelpersPath;
        }

        // Start output buffering
        ob_start();

        try {
            // Include the view file
            include $viewPath;
            $content = ob_get_clean();

            // Wrap with layout if exists
            if (isset($__layout) && $__layout) {
                $layoutPath = $basePath . '/layouts/' . $__layout . '.php';
                if (file_exists($layoutPath)) {
                    $__content = $content;
                    ob_start();
                    include $layoutPath;
                    $content = ob_get_clean();
                }
            } else {
                // Default layout
                $layoutPath = $basePath . '/layouts/app.php';
                if (file_exists($layoutPath)) {
                    $__content = $content;
                    ob_start();
                    include $layoutPath;
                    $content = ob_get_clean();
                }
            }

            return $content;
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }
    }
}

if (!function_exists('response')) {
    /**
     * Create a response
     * 
     * @param string $content Response content
     * @param int $status Status code
     * @param array $headers Response headers
     * @return \FF\Framework\Http\Response
     */
    function response(string $content = '', int $status = 200, array $headers = []): \FF\Framework\Http\Response
    {
        return new \FF\Framework\Http\Response($content, $status, $headers);
    }
}

if (!function_exists('cache')) {
    /**
     * Get cache instance
     */
    function cache()
    {
        return app('cache');
    }
}

if (!function_exists('logger')) {
    /**
     * Get logger instance
     */
    function logger()
    {
        return app('logger');
    }
}

if (!function_exists('session')) {
    /**
     * Get session instance or value from session
     * 
     * @param string|null $key The session key to retrieve
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Session instance or value
     */
    function session(?string $key = null, $default = null)
    {
        $sessionManager = app('session');
        
        // If no key provided, return the session manager instance
        if ($key === null) {
            return $sessionManager;
        }
        
        // If key provided, get value from session
        return $sessionManager->get($key, $default);
    }
}

if (!function_exists('base_path')) {
    /**
     * Get base path
     */
    function base_path(string $path = ''): string
    {
        return BASE_PATH . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get storage path
     */
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('asset')) {
    /**
     * Get asset URL
     */
    function asset(string $path): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('route')) {
    /**
     * Generate URL for a route
     */
    function route(string $name, array $parameters = []): string
    {
        $router = app('router');
        return $router->url($name, $parameters);
    }
}

if (!function_exists('redirect')) {
    /**
     * Create a redirect response
     * 
     * @param string $url The URL to redirect to
     * @param int $status Status code (302 by default)
     * @return \FF\Framework\Http\Response
     */
    function redirect(string $url, int $status = 302): \FF\Framework\Http\Response
    {
        return response()->redirect($url, $status);
    }
}
