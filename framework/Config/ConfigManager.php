<?php

namespace FF\Framework\Config;

/**
 * ConfigManager - Configuration Manager
 * 
 * Manages application configuration with nested access support.
 * Loads configuration from PHP files and provides cache for performance.
 */
class ConfigManager
{
    /**
     * All loaded configurations
     * 
     * @var array
     */
    protected array $configs = [];

    /**
     * Whether config is cached
     * 
     * @var bool
     */
    protected bool $cached = false;

    /**
     * Create a new ConfigManager instance
     * 
     * @param array $configs Initial configuration
     */
    public function __construct(array $configs = [])
    {
        $this->configs = $configs;
    }

    /**
     * Get a configuration value using dot notation
     * 
     * @param string $key The config key (e.g., 'database.driver')
     * @param mixed $default Default value
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $parts = explode('.', $key);
        $value = $this->configs;

        foreach ($parts as $part) {
            if (is_array($value) && isset($value[$part])) {
                $value = $value[$part];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Set a configuration value
     * 
     * @param string $key The config key
     * @param mixed $value The value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $parts = explode('.', $key);
        $config = &$this->configs;

        foreach ($parts as $part) {
            if (!isset($config[$part])) {
                $config[$part] = [];
            }
            $config = &$config[$part];
        }

        $config = $value;
    }

    /**
     * Load configuration from a file
     * 
     * @param string $filePath The configuration file path
     * @param string $key Optional key to store under
     * @return void
     */
    public function load(string $filePath, string $key = ''): void
    {
        if (!file_exists($filePath)) {
            throw new \Exception("Configuration file not found: $filePath");
        }

        $config = require $filePath;

        if ($key) {
            $this->set($key, $config);
        } else {
            // Load config files from directory
            $this->configs = array_merge($this->configs, $config);
        }
    }

    /**
     * Load all configuration files from a directory
     * 
     * @param string $directory The config directory path
     * @return void
     */
    public function loadDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            throw new \Exception("Configuration directory not found: $directory");
        }

        $files = scandir($directory);
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (substr($file, -4) === '.php') {
                $key = substr($file, 0, -4);
                $filePath = $directory . '/' . $file;
                $config = require $filePath;
                $this->set($key, $config);
            }
        }
    }

    /**
     * Check if configuration exists
     * 
     * @param string $key The config key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Get all configurations
     * 
     * @return array
     */
    public function all(): array
    {
        return $this->configs;
    }

    /**
     * Get a configuration group
     * 
     * @param string $group The group name
     * @return array|null
     */
    public function getGroup(string $group): ?array
    {
        return $this->configs[$group] ?? null;
    }

    /**
     * Cache configuration (for performance)
     * 
     * @param string $cacheFile The cache file path
     * @return bool Success
     */
    public function cache(string $cacheFile): bool
    {
        $code = '<?php return ' . var_export($this->configs, true) . ';';
        $result = file_put_contents($cacheFile, $code);
        $this->cached = true;
        return $result !== false;
    }

    /**
     * Check if config is cached
     * 
     * @return bool
     */
    public function isCached(): bool
    {
        return $this->cached;
    }

    /**
     * Clear cache
     * 
     * @return void
     */
    public function clearCache(): void
    {
        $this->cached = false;
    }
}
