<?php

namespace FF\Security;

use FF\Cache\Cache;

/**
 * RateLimiter - Rate Limiting
 * 
 * Limits the number of requests from a client within a time window.
 * Uses cache for storing attempt counts.
 */
class RateLimiter
{
    /**
     * The cache instance
     * 
     * @var Cache
     */
    protected Cache $cache;

    /**
     * Create a new RateLimiter instance
     * 
     * @param Cache $cache The cache instance
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Check if the request is rate limited
     * 
     * @param string $key The rate limit key (e.g., user ID or IP)
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $decayMinutes Minutes until limit resets
     * @return bool True if rate limited
     */
    public function isLimited(string $key, int $maxAttempts = 60, int $decayMinutes = 1): bool
    {
        $cacheKey = $this->cacheKey($key);

        return $this->withLock($cacheKey, function () use ($cacheKey, $maxAttempts, $decayMinutes) {
            $state = $this->getState($cacheKey, $decayMinutes);

            return $state['attempts'] >= $maxAttempts;
        });
    }

    /**
     * Record an attempt
     * 
     * @param string $key The rate limit key
     * @param int $decayMinutes Minutes until limit resets
     * @return int The updated attempt count
     */
    public function recordAttempt(string $key, int $decayMinutes = 1): int
    {
        $cacheKey = $this->cacheKey($key);

        return $this->withLock($cacheKey, function () use ($cacheKey, $decayMinutes) {
            $state = $this->getState($cacheKey, $decayMinutes);

            $state['attempts']++;
            $state['expires_at'] = time() + ($decayMinutes * 60);

            $this->storeState($cacheKey, $state, $decayMinutes);

            return $state['attempts'];
        });
    }

    /**
     * Get the number of remaining attempts
     * 
     * @param string $key The rate limit key
     * @param int $maxAttempts Maximum attempts allowed
     * @return int Remaining attempts
     */
    public function getRemaining(string $key, int $maxAttempts = 60): int
    {
        $cacheKey = $this->cacheKey($key);
        $state = $this->getState($cacheKey);

        return max(0, $maxAttempts - $state['attempts']);
    }

    /**
     * Clear the rate limit for a key
     * 
     * @param string $key The rate limit key
     * @return void
     */
    public function reset(string $key): void
    {
        $cacheKey = $this->cacheKey($key);

        $this->withLock($cacheKey, function () use ($cacheKey) {
            $this->cache->forget($cacheKey);
        });
    }

    /**
     * Get time until rate limit resets (in seconds)
     * 
     * @param string $key The rate limit key
     * @param int $decayMinutes The decay window in minutes
     * @return int Seconds until reset
     */
    public function getRetryAfter(string $key, int $decayMinutes = 1): int
    {
        $cacheKey = $this->cacheKey($key);
        $state = $this->getState($cacheKey, $decayMinutes);

        if ($state['attempts'] === 0 || $state['expires_at'] === null) {
            return 0;
        }

        $remaining = $state['expires_at'] - time();

        return $remaining > 0 ? $remaining : 0;
    }

    /**
     * Limit a request by IP address
     * 
     * @param string $ip The IP address
     * @param int $maxAttempts Maximum attempts
     * @param int $decayMinutes Decay time in minutes
     * @return bool True if limited
     */
    public function limitByIp(string $ip, int $maxAttempts = 60, int $decayMinutes = 1): bool
    {
        return $this->isLimited("ip:{$ip}", $maxAttempts, $decayMinutes);
    }

    /**
     * Limit a request by user ID
     * 
     * @param int $userId The user ID
     * @param int $maxAttempts Maximum attempts
     * @param int $decayMinutes Decay time in minutes
     * @return bool True if limited
     */
    public function limitByUser(int $userId, int $maxAttempts = 60, int $decayMinutes = 1): bool
    {
        return $this->isLimited("user:{$userId}", $maxAttempts, $decayMinutes);
    }

    /**
     * Limit a request by endpoint
     * 
     * @param string $endpoint The endpoint (controller@method or URI)
     * @param int $maxAttempts Maximum attempts
     * @param int $decayMinutes Decay time in minutes
     * @return bool True if limited
     */
    public function limitByEndpoint(string $endpoint, int $maxAttempts = 100, int $decayMinutes = 1): bool
    {
        return $this->isLimited("endpoint:{$endpoint}", $maxAttempts, $decayMinutes);
    }

    /**
     * Generate namespaced cache key.
     */
    protected function cacheKey(string $key): string
    {
        return "rate_limit:" . $key;
    }

    /**
     * Retrieve the current state from cache, normalising data.
     */
    protected function getState(string $cacheKey, int $decayMinutes = 1): array
    {
        $state = $this->cache->get($cacheKey);

        if (!is_array($state) ||
            !isset($state['attempts']) ||
            !isset($state['expires_at']) ||
            !is_int($state['attempts']) ||
            (!is_int($state['expires_at']) && !is_null($state['expires_at']))
        ) {
            $state = [
                'attempts' => 0,
                'expires_at' => null,
            ];
        }

        if ($state['expires_at'] !== null && $state['expires_at'] <= time()) {
            $state = [
                'attempts' => 0,
                'expires_at' => time() + ($decayMinutes * 60),
            ];
            $this->storeState($cacheKey, $state, $decayMinutes);
        }

        return $state;
    }

    /**
     * Persist rate limit state into cache.
     */
    protected function storeState(string $cacheKey, array $state, int $decayMinutes): void
    {
        $this->cache->put($cacheKey, $state, $decayMinutes * 60);
    }

    /**
     * Execute callback with a file-based mutex to ensure atomic operations.
     *
     * @template T
     * @param callable():T $callback
     * @return mixed
     */
    protected function withLock(string $cacheKey, callable $callback)
    {
        $lockPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ff_rate_limit_' . sha1($cacheKey) . '.lock';
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
