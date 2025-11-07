<?php

use FF\Security\CsrfGuard;

use FF\View\HtmlValue;

if (!function_exists('config')) {
    require_once __DIR__ . '/Support/helpers.php';
}

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
        $realBase = realpath($basePath);

        if ($realBase === false) {
            throw new \RuntimeException('View directory not found.');
        }

        $normalize = static function (string $path): ?string {
            $rawSegments = explode('/', str_replace('\\', '/', $path));
            $segments = [];

            foreach ($rawSegments as $segment) {
                if ($segment === '' || $segment === '.') {
                    continue;
                }

                if ($segment === '..') {
                    if (empty($segments)) {
                        return null;
                    }
                    array_pop($segments);
                    continue;
                }

                $segments[] = $segment;
            }

            if (empty($segments)) {
                return null;
            }

            $lastIndex = count($segments) - 1;
            foreach ($segments as $index => $segment) {
                $pattern = $index === $lastIndex
                    ? '/^[A-Za-z0-9_\-]+(\.php)?$/'
                    : '/^[A-Za-z0-9_\-]+$/';

                if (!preg_match($pattern, $segment)) {
                    return null;
                }
            }

            return implode('/', $segments);
        };

        $resolve = static function (string $base, string $viewName, bool $required = true) use ($normalize): ?string {
            $relative = str_replace('.', '/', $viewName);
            if (substr($relative, -4) !== '.php') {
                $relative .= '.php';
            }

            $normalized = $normalize($relative);
            if ($normalized === null) {
                if ($required) {
                    throw new \InvalidArgumentException("Invalid view name: {$viewName}");
                }
                return null;
            }

            $fullPath = $base . DIRECTORY_SEPARATOR . $normalized;
            if (!file_exists($fullPath)) {
                if ($required) {
                    throw new \Exception("View not found: {$viewName}");
                }
                return null;
            }

            $basePrefix = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $realPath = realpath($fullPath);
            if ($realPath === false || strpos($realPath, $basePrefix) !== 0) {
                if ($required) {
                    throw new \Exception("View not found: {$viewName}");
                }
                return null;
            }

            return $realPath;
        };

        $viewPath = $resolve($realBase, $name);

        if ($viewPath === null) {
            throw new \Exception("View not found: {$name}");
        }

        $prepareVariables = static function (array $variables): array {
            $prepared = [];

            foreach ($variables as $key => $value) {
                // New policy: do NOT modify developer-provided values.
                // Escaping is explicit via h() and raw_html().
                $prepared[$key] = $value;
            }

            return $prepared;
        };

        // Extract data into variables with automatic escaping
        extract($prepareVariables($data), EXTR_SKIP);

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
                $layoutPath = $resolve($realBase, 'layouts/' . $__layout, false);
                if ($layoutPath) {
                    $__content = $content;
                    ob_start();
                    include $layoutPath;
                    $content = ob_get_clean();
                }
            } else {
                // Default layout
                $layoutPath = $resolve($realBase, 'layouts/app', false);
                if ($layoutPath) {
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
     * @return \FF\Http\Response
     */
    function response(string $content = '', int $status = 200, array $headers = []): \FF\Http\Response
    {
        $response = new \FF\Http\Response($content, $status, $headers);
        if (!empty($headers)) {
            $response->withHeaders($headers);
        }

        return $response;
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Retrieve the current CSRF token value.
     */
    function csrf_token(): string
    {
        /** @var CsrfGuard $guard */
        $guard = app(CsrfGuard::class);
        return $guard->token();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Render the CSRF hidden field for HTML forms.
     */
    function csrf_field(): string
    {
        /** @var CsrfGuard $guard */
        $guard = app(CsrfGuard::class);
        return $guard->field();
    }
}

if (!function_exists('csrf_header')) {
    /**
     * Get the CSRF header name/value pair for API requests.
     *
     * @return array{name: string, value: string}
     */
    function csrf_header(): array
    {
        /** @var CsrfGuard $guard */
        $guard = app(CsrfGuard::class);
        return [
            'name' => CsrfGuard::getHeaderName(),
            'value' => $guard->token(),
        ];
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

if (!function_exists('request_id')) {
    /**
     * Get current request correlation ID.
     * Returns null if not set.
     */
    function request_id(): ?string
    {
        try {
            $app = app();
            if (method_exists($app, 'has') && $app->has('request_id')) {
                $id = $app->make('request_id');
                return is_string($id) ? $id : null;
            }
        } catch (\Throwable $e) {
            // no-op
        }
        return null;
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
        if (!$sessionManager->isStarted()) {
            $sessionManager->start();
        }
        
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

if (!function_exists('url')) {
    /**
     * Generate an absolute URL based on APP_URL and provided path.
     *
     * Examples:
     * - url('/users') => 'http://localhost/users' (assuming APP_URL is http://localhost)
     * - url('users')  => 'http://localhost/users'
     * - url('')       => base APP_URL, e.g. 'http://localhost'
     * - url('http://example.com/path') => returns the given absolute URL
     */
    function url(string $path = ''): string
    {
        // Base URL from config with sane default
        $base = (string)(config('app.url', 'http://localhost') ?? 'http://localhost');
        $base = trim($base);
        $base = rtrim($base, '/');

        // Validate base URL format; fallback to default if invalid
        if (!filter_var($base, FILTER_VALIDATE_URL)) {
            $base = 'http://localhost';
        }

        // Normalize and sanitize input path
        $path = preg_replace('/[\x00-\x1F\x7F]/u', '', $path);
        $path = trim($path);

        // If input is an absolute URL, return as-is
        if ($path !== '' && preg_match('#^https?://#i', $path)) {
            return $path;
        }

        // Root-relative or relative path handling
        if ($path === '') {
            return $base;
        }

        // Ensure single leading slash and collapse duplicate separators
        $normalized = preg_replace('#[/\\]+#', '/', $path);
        if (!str_starts_with($normalized, '/')) {
            $normalized = '/' . ltrim($normalized, '/');
        }

        return $base . $normalized;
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
     * @return \FF\Http\Response
     */
    function redirect(string $url, int $status = 302): \FF\Http\Response
    {
        // Step 1: Normalize URL - remove control characters and excessive whitespace
        $url = preg_replace('/[\x00-\x1F\x7F]/u', '', $url);
        $url = trim($url);

        if (empty($url)) {
            throw new \InvalidArgumentException('Redirect URL cannot be empty.');
        }

        // Step 2: Check for dangerous schemes
        $dangerousSchemes = ['javascript:', 'data:', 'vbscript:', 'file:', 'about:'];
        $lowerUrl = strtolower($url);
        foreach ($dangerousSchemes as $scheme) {
            if (str_starts_with($lowerUrl, $scheme)) {
                throw new \InvalidArgumentException('Redirect URL contains a dangerous scheme.');
            }
        }

        // Step 3: Handle relative/root-relative URLs
        if (str_starts_with($url, '/')) {
            // Block protocol-relative URLs (//example.com)
            if (str_starts_with($url, '//')) {
                throw new \InvalidArgumentException('Protocol-relative URLs are not allowed.');
            }

            // Normalize path: remove backslashes and consecutive slashes
            $normalized = preg_replace('#[/\\\\]+#', '/', $url);
            
            // Ensure it still starts with single slash
            if (!str_starts_with($normalized, '/') || str_starts_with($normalized, '//')) {
                throw new \InvalidArgumentException('Invalid relative URL format.');
            }

            return response()->redirect($normalized, $status);
        }

        // Step 4: Validate absolute URLs
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid URL format.');
        }

        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['scheme']) || empty($parsed['host'])) {
            throw new \InvalidArgumentException('URL must have a valid scheme and host.');
        }

        // Step 5: Validate scheme for absolute URLs
        $allowedSchemes = ['http', 'https'];
        if (!in_array(strtolower($parsed['scheme']), $allowedSchemes, true)) {
            throw new \InvalidArgumentException('Only HTTP and HTTPS schemes are allowed.');
        }

        // Step 6: Validate host against whitelist
        $allowedHosts = array_filter(array_map('trim', (array)(config('app.allowed_hosts') ?? [])));
        if (empty($allowedHosts)) {
            throw new \InvalidArgumentException('No allowed hosts configured. Set APP_ALLOWED_HOSTS.');
        }

        // Normalize host (lowercase, remove port if comparing with allowed hosts)
        $urlHost = strtolower($parsed['host']);
        $allowedHosts = array_map('strtolower', $allowedHosts);

        if (!in_array($urlHost, $allowedHosts, true)) {
            throw new \InvalidArgumentException('Redirect host "' . $urlHost . '" is not in the allowed hosts list.');
        }

        return response()->redirect($url, $status);
    }
}
