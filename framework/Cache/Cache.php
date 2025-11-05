<?php

namespace FF\Framework\Cache;

/**
 * Cache - Cache Manager
 * 
 * Provides caching functionality with support for multiple drivers
 * (File, Array, and Database). Manages cache key storage and retrieval.
 */
class Cache
{
    /**
     * The cache driver
     * 
     * @var string
     */
    protected string $driver;

    /**
     * The cache directory
     * 
     * @var string
     */
    protected string $cacheDir;

    /**
     * In-memory cache store
     * 
     * @var array
     */
    protected static array $store = [];

    /**
     * Create a new Cache instance
     * 
     * @param string $driver The cache driver (file, array, database)
     * @param string $cacheDir The cache directory
     */
    public function __construct(string $driver = 'array', string $cacheDir = '')
    {
        $this->driver = $driver;
        $this->cacheDir = $cacheDir ?: sys_get_temp_dir() . '/cache';

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Get a value from cache
     * 
     * @param string $key The cache key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        match ($this->driver) {
            'array' => $this->getFromArray($key, $default),
            'file' => $this->getFromFile($key, $default),
            default => $default,
        };

        return match ($this->driver) {
            'array' => $this->getFromArray($key, $default),
            'file' => $this->getFromFile($key, $default),
            default => $default,
        };
    }

    /**
     * Put a value into cache
     * 
     * @param string $key The cache key
     * @param mixed $value The value
     * @param int $minutes Time in minutes before expiration (0 = no expiration)
     * @return void
     */
    public function put(string $key, $value, int $minutes = 0): void
    {
        match ($this->driver) {
            'array' => $this->putInArray($key, $value, $minutes),
            'file' => $this->putInFile($key, $value, $minutes),
        };
    }

    /**
     * Remember a value or store it if not cached
     * 
     * @param string $key The cache key
     * @param int $minutes Time in minutes
     * @param callable $callback Callback to get value if not cached
     * @return mixed
     */
    public function remember(string $key, int $minutes, callable $callback)
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->put($key, $value, $minutes);

        return $value;
    }

    /**
     * Remove a value from cache
     * 
     * @param string $key The cache key
     * @return void
     */
    public function forget(string $key): void
    {
        match ($this->driver) {
            'array' => $this->forgetFromArray($key),
            'file' => $this->forgetFromFile($key),
        };
    }

    /**
     * Clear all cache
     * 
     * @return void
     */
    public function flush(): void
    {
        match ($this->driver) {
            'array' => $this->flushArray(),
            'file' => $this->flushFile(),
        };
    }

    // Array driver methods

    /**
     * Get from array cache
     * 
     * @param string $key The key
     * @param mixed $default Default value
     * @return mixed
     */
    protected function getFromArray(string $key, $default = null)
    {
        if (!isset(self::$store[$key])) {
            return $default;
        }

        $item = self::$store[$key];

        // Check if expired
        if ($item['expires_at'] !== null && time() > $item['expires_at']) {
            unset(self::$store[$key]);
            return $default;
        }

        return $item['value'];
    }

    /**
     * Put into array cache
     * 
     * @param string $key The key
     * @param mixed $value The value
     * @param int $minutes Expiration time
     * @return void
     */
    protected function putInArray(string $key, $value, int $minutes): void
    {
        self::$store[$key] = [
            'value' => $value,
            'expires_at' => $minutes > 0 ? time() + ($minutes * 60) : null,
        ];
    }

    /**
     * Forget from array cache
     * 
     * @param string $key The key
     * @return void
     */
    protected function forgetFromArray(string $key): void
    {
        unset(self::$store[$key]);
    }

    /**
     * Flush array cache
     * 
     * @return void
     */
    protected function flushArray(): void
    {
        self::$store = [];
    }

    // File driver methods

    /**
     * Get from file cache
     * 
     * @param string $key The key
     * @param mixed $default Default value
     * @return mixed
     */
    protected function getFromFile(string $key, $default = null)
    {
        $file = $this->getCacheFile($key);

        if (!file_exists($file)) {
            return $default;
        }

        $content = file_get_contents($file);
        $item = json_decode($content, true);

        if (!$item) {
            return $default;
        }

        // Check if expired
        if ($item['expires_at'] !== null && time() > $item['expires_at']) {
            unlink($file);
            return $default;
        }

        return $item['value'];
    }

    /**
     * Put into file cache
     * 
     * @param string $key The key
     * @param mixed $value The value
     * @param int $minutes Expiration time
     * @return void
     */
    protected function putInFile(string $key, $value, int $minutes): void
    {
        $file = $this->getCacheFile($key);
        $item = [
            'value' => $value,
            'expires_at' => $minutes > 0 ? time() + ($minutes * 60) : null,
        ];

        file_put_contents($file, json_encode($item), LOCK_EX);
    }

    /**
     * Forget from file cache
     * 
     * @param string $key The key
     * @return void
     */
    protected function forgetFromFile(string $key): void
    {
        $file = $this->getCacheFile($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Flush file cache
     * 
     * @return void
     */
    protected function flushFile(): void
    {
        $files = glob($this->cacheDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Get the cache file path for a key
     * 
     * @param string $key The cache key
     * @return string The file path
     */
    protected function getCacheFile(string $key): string
    {
        return $this->cacheDir . '/' . hash('sha256', $key) . '.cache';
    }
}
