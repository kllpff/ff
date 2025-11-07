<?php

namespace FF\Cache;
use FF\Exceptions\CacheException;

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
        try {
            switch ($this->driver) {
                case 'array':
                    return $this->getFromArray($key, $default);
                case 'file':
                    return $this->getFromFile($key, $default);
                default:
                    return $default;
            }
        } catch (\Throwable $e) {
            try { \logger()->error('Cache get failed', [
                'key' => $key,
                'driver' => $this->driver,
                'error' => $e->getMessage(),
            ]); } catch (\Throwable $logError) {}
            throw new CacheException('Cache get failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Put a value into cache
     * 
     * @param string $key The cache key
     * @param mixed $value The value
     * @param int $seconds Time in seconds before expiration (0 = no expiration)
     * @return void
     */
    public function put(string $key, $value, int $seconds = 0): void
    {
        try {
            switch ($this->driver) {
                case 'array':
                    $this->putInArray($key, $value, $seconds);
                    break;
                case 'file':
                    $this->putInFile($key, $value, $seconds);
                    break;
            }
        } catch (\Throwable $e) {
            try { \logger()->error('Cache put failed', [
                'key' => $key,
                'driver' => $this->driver,
                'seconds' => $seconds,
                'error' => $e->getMessage(),
            ]); } catch (\Throwable $logError) {}
            throw new CacheException('Cache put failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Remember a value or store it if not cached, with locking to prevent cache stampede.
     * 
     * @param string $key The cache key
     * @param int $seconds Time in seconds
     * @param callable $callback Callback to get value if not cached
     * @return mixed
     */
    public function remember(string $key, int $seconds, callable $callback)
    {
        try {
            $value = $this->get($key);
            if ($value !== null) { return $value; }

            return $this->withLock($key, function () use ($key, $seconds, $callback) {
                $inner = $this->get($key);
                if ($inner !== null) { return $inner; }

                try {
                    $computed = $callback();
                    $this->put($key, $computed, $seconds);
                    return $computed;
                } catch (\Throwable $e) {
                    try { \logger()->error('Cache remember callback/put failed', [
                        'key' => $key,
                        'driver' => $this->driver,
                        'error' => $e->getMessage(),
                    ]); } catch (\Throwable $logError) {}
                    throw new CacheException('Cache remember failed: ' . $e->getMessage(), 0, $e);
                }
            });
        } catch (\Throwable $e) {
            try { \logger()->error('Cache remember failed', [
                'key' => $key,
                'driver' => $this->driver,
                'error' => $e->getMessage(),
            ]); } catch (\Throwable $logError) {}
            throw new CacheException('Cache remember failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Remove a value from cache
     * 
     * @param string $key The cache key
     * @return void
     */
    public function forget(string $key): void
    {
        try {
            switch ($this->driver) {
                case 'array':
                    $this->forgetFromArray($key);
                    break;
                case 'file':
                    $this->forgetFromFile($key);
                    break;
            }
        } catch (\Throwable $e) {
            try { \logger()->error('Cache forget failed', [
                'key' => $key,
                'driver' => $this->driver,
                'error' => $e->getMessage(),
            ]); } catch (\Throwable $logError) {}
            throw new CacheException('Cache forget failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Clear all cache
     * 
     * @return void
     */
    public function flush(): void
    {
        try {
            switch ($this->driver) {
                case 'array':
                    $this->flushArray();
                    break;
                case 'file':
                    $this->flushFile();
                    break;
            }
        } catch (\Throwable $e) {
            try { \logger()->error('Cache flush failed', [
                'driver' => $this->driver,
                'cache_dir' => $this->cacheDir,
                'error' => $e->getMessage(),
            ]); } catch (\Throwable $logError) {}
            throw new CacheException('Cache flush failed: ' . $e->getMessage(), 0, $e);
        }
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
    protected function putInArray(string $key, $value, int $seconds): void
    {
        self::$store[$key] = [
            'value' => $this->prepareValueForStorage($value),
            'expires_at' => $seconds > 0 ? time() + $seconds : null,
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
        try {
            if (!file_exists($file)) { return $default; }

            $item = $this->readJson($file);
            if ($item === null) { return $default; }

            if ($item['expires_at'] !== null && time() > $item['expires_at']) {
                try { unlink($file); } catch (\Throwable $unlinkError) {
                    try { \logger()->error('Cache expired file unlink failed', [
                        'key' => $key,
                        'file' => $file,
                        'error' => $unlinkError->getMessage(),
                    ]); } catch (\Throwable $logError) {}
                }
                return $default;
            }

            return $item['value'];
        } catch (\Throwable $e) {
            try { \logger()->error('Cache read file failed', [
                'key' => $key,
                'file' => $file,
                'error' => $e->getMessage(),
            ]); } catch (\Throwable $logError) {}
            throw new CacheException('Cache read failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Put into file cache
     * 
     * @param string $key The key
     * @param mixed $value The value
     * @param int $minutes Expiration time
     * @return void
     */
    protected function putInFile(string $key, $value, int $seconds): void
    {
        $file = $this->getCacheFile($key);
        try {
            $item = [
                'value' => $this->prepareValueForStorage($value),
                'expires_at' => $seconds > 0 ? time() + $seconds : null,
            ];
            $this->writeJson($file, $item);
        } catch (\Throwable $e) {
            try { \logger()->error('Cache write file failed', [
                'key' => $key,
                'file' => $file,
                'error' => $e->getMessage(),
            ]); } catch (\Throwable $logError) {}
            throw new CacheException('Cache write failed: ' . $e->getMessage(), 0, $e);
        }
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
        try {
            if (file_exists($file)) { unlink($file); }
        } catch (\Throwable $e) {
            try { \logger()->error('Cache delete file failed', [
                'key' => $key,
                'file' => $file,
                'error' => $e->getMessage(),
            ]); } catch (\Throwable $logError) {}
            throw new CacheException('Cache delete failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Flush file cache
     * 
     * @return void
     */
    protected function flushFile(): void
    {
        try {
            $files = glob($this->cacheDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    try { unlink($file); } catch (\Throwable $e) {
                        try { \logger()->error('Cache flush unlink failed', [
                            'file' => $file,
                            'error' => $e->getMessage(),
                        ]); } catch (\Throwable $logError) {}
                    }
                }
            }
        } catch (\Throwable $e) {
            try { \logger()->error('Cache flush failed', [
                'cache_dir' => $this->cacheDir,
                'error' => $e->getMessage(),
            ]); } catch (\Throwable $logError) {}
            throw new CacheException('Cache flush failed: ' . $e->getMessage(), 0, $e);
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

    /**
     * Ensure cache value is safe for storage by disallowing objects and resources.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function prepareValueForStorage($value)
    {
        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if ($value instanceof \JsonSerializable) {
            return $this->prepareValueForStorage($value->jsonSerialize());
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        if ($value instanceof \Traversable) {
            $value = iterator_to_array($value);
        }

        if (is_array($value)) {
            $sanitized = [];
            foreach ($value as $key => $item) {
                $sanitized[$key] = $this->prepareValueForStorage($item);
            }
            return $sanitized;
        }

        if (is_object($value) || is_resource($value)) {
            throw new \InvalidArgumentException('Cache values must be scalar, array, or JsonSerializable.');
        }

        return $value;
    }

    /**
     * Write JSON encoded data to cache file with locking.
     */
    protected function writeJson(string $file, array $data): void
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('Failed to encode cache payload.');
        }

        file_put_contents($file, $json, LOCK_EX);
    }

    /**
     * Safely decode JSON from cache file.
     */
    protected function readJson(string $file): ?array
    {
        $content = file_get_contents($file);
        if ($content === false) {
            return null;
        }

        $data = json_decode($content, true);
        if (!is_array($data) || !array_key_exists('value', $data) || !array_key_exists('expires_at', $data)) {
            return null;
        }

        return $data;
    }

    /**
     * Check if a cache key exists and is not expired
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        try {
            switch ($this->driver) {
                case 'array':
                    return $this->hasInArray($key);
                case 'file':
                    return $this->hasInFile($key);
                default:
                    return false;
            }
        } catch (\Throwable $e) {
            try { \logger()->error('Cache has failed', [
                'key' => $key,
                'driver' => $this->driver,
                'error' => $e->getMessage(),
            ]); } catch (\Throwable $logError) {}
            return false;
        }
    }

    /**
     * Check existence in array driver
     */
    protected function hasInArray(string $key): bool
    {
        if (!isset(self::$store[$key])) { return false; }
        $item = self::$store[$key];
        if ($item['expires_at'] !== null && time() > $item['expires_at']) {
            unset(self::$store[$key]);
            return false;
        }
        return true;
    }

    /**
     * Check existence in file driver
     */
    protected function hasInFile(string $key): bool
    {
        $file = $this->getCacheFile($key);
        if (!file_exists($file)) { return false; }
        $item = $this->readJson($file);
        if ($item === null) { return false; }
        if ($item['expires_at'] !== null && time() > $item['expires_at']) {
            try { unlink($file); } catch (\Throwable $unlinkError) {
                try { \logger()->error('Cache expired file unlink failed', [
                    'key' => $key,
                    'file' => $file,
                    'error' => $unlinkError->getMessage(),
                ]); } catch (\Throwable $logError) {}
            }
            return false;
        }
        return true;
    }

    /**
     * Execute callback with a file-based mutex to ensure atomic operations.
     *
     * @template T
     * @param string $key
     * @param callable():T $callback
     * @return mixed
     */
    protected function withLock(string $key, callable $callback)
    {
        $lockPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ff_cache_' . sha1($key) . '.lock';
        $handle = fopen($lockPath, 'c');
    
        if ($handle === false) {
            return $callback();
        }
    
        try {
            if (!flock($handle, LOCK_EX)) {
                return $callback();
            }
    
            return $callback();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}
