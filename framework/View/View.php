<?php

namespace FF\View;

use FF\Core\Container;
use FF\View\HtmlValue;

/**
 * View - Template View Engine
 * 
 * Manages view rendering, variable passing, and template compilation.
 * Supports simple PHP-based templates with variable interpolation.
 */
class View
{
    /**
     * The view path
     * 
     * @var string
     */
    protected string $path;

    /**
     * The view variables
     * 
     * @var array
     */
    protected array $data = [];

    /**
     * The container instance
     * 
     * @var Container
     */
    protected Container $container;

    /**
     * The view name (primarily for debugging/logging when created via make()).
     *
     * @var string|null
     */
    protected ?string $viewName = null;

    /**
     * Create a new View instance
     * 
     * @param Container $container The container
     * @param string $viewPath The base view path
     */
    public function __construct(Container $container, string $viewPath)
    {
        $this->container = $container;
        $this->path = $viewPath;
    }

    /**
     * Render a view file
     * 
     * @param string $view The view file (relative to view path)
     * @param array $data View variables
     * @return string The rendered view
     */
    public function render(string $view, array $data = []): string
    {
        $this->data = array_merge($this->data, $data);
        
        $filePath = $this->resolveViewPath($view);

        // Extract variables for use in template (auto-escaped)
        $variables = $this->prepareVariables(array_merge(static::$shared, $this->data, $data));
        
        // Protect critical variables from being overwritten
        $protected = ['this', 'app', 'filePath', 'view', '__DIR__', '__FILE__', '__LINE__', '__FUNCTION__', '__CLASS__', '__METHOD__', '__NAMESPACE__'];
        $variables = array_diff_key($variables, array_flip($protected));
        
        extract($variables, EXTR_SKIP);

        // Start output buffering and include the view
        ob_start();
        include $filePath;
        $content = ob_get_clean();

        return $content;
    }

    /**
     * Set a view variable
     * 
     * @param string $key The variable name
     * @param mixed $value The value
     * @return self
     */
    public function with(string $key, $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Set multiple view variables
     * 
     * @param array $data The variables
     * @return self
     */
    public function withData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Share data with all views
     * 
     * @param string $key The variable name
     * @param mixed $value The value
     * @return void
     */
    public static function share(string $key, $value): void
    {
        static::$shared[$key] = $value;
    }

    /**
     * Shared data across all views
     * 
     * @var array
     */
    protected static array $shared = [];

    /**
     * Include a view within another view
     * 
     * @param string $view The view file
     * @param array $data View variables
     * @return string The rendered view
     */
    public function include(string $view, array $data = []): string
    {
        return $this->render($view, array_merge(static::$shared, $data));
    }

    /**
     * Output escaped content
     * 
     * @param mixed $content The content to escape
     * @return void
     */
    public function escape($content): void
    {
        if ($content instanceof HtmlValue) {
            echo $content->escape();
            return;
        }

        echo htmlspecialchars((string)$content, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get a view variable
     * 
     * @param string $key The variable name
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * Check if a view variable exists
     * 
     * @param string $key The variable name
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Make a view (factory method)
     * 
     * @param string $view The view file
     * @param array $data View variables
     * @return static
     */
    public static function make(string $view, array $data = [])
    {
        $instance = new static(app(), app()->basePath('resources/views'));
        $instance->data = array_merge(static::$shared, $data);
        $instance->viewName = $view;
        return $instance;
    }



    /**
     * Resolve a view path while preventing directory traversal.
     */
    protected function resolveViewPath(string $view): string
    {
        $relativePath = str_replace('.', '/', $view) . '.php';
        $normalized = $this->normalizePath($relativePath);

        if ($normalized === null) {
            throw new \InvalidArgumentException("Invalid view name: {$view}");
        }

        $fullPath = $this->path . '/' . $normalized;

        $realBase = realpath($this->path);
        if ($realBase === false) {
            throw new \RuntimeException('View base path not found.');
        }

        if (!file_exists($fullPath)) {
            throw new \Exception("View file not found: {$view}");
        }

        $realPath = realpath($fullPath);
        if ($realPath === false) {
            throw new \Exception("View file not found: {$view}");
        }

        $basePrefix = rtrim($realBase, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (strpos($realPath, $basePrefix) !== 0) {
            throw new \Exception("View file not found: {$view}");
        }

        return $realPath;
    }

    /**
     * Normalize a path by removing traversal sequences.
     */
    protected function normalizePath(string $path): ?string
    {
        $rawSegments = explode('/', str_replace('\\', '/', $path));
        $segments = [];
        if ($path === '') {
            return null;
        }
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
    }

    /**
     * Prepare view variables with automatic HTML escaping.
     *
     * @param array $data
     * @return array
     */
    protected function prepareVariables(array $data): array
    {
        $prepared = [];

        foreach ($data as $key => $value) {
            $prepared[$key] = $this->sanitizeValue($value);
        }

        return $prepared;
    }

    /**
     * Sanitize a single view value.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function sanitizeValue($value)
    {
        // New policy: do NOT auto-escape. Respect developer-provided values.
        // HtmlValue instances retain their behavior (raw vs escaped via helper usage).
        return $value;
    }
}
