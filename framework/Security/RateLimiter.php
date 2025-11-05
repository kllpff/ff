<?php

namespace FF\Framework\Security;

use FF\Framework\Cache\Cache;

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
        $cacheKey = "rate_limit:{$key}";
        $attempts = (int)$this->cache->get($cacheKey, 0);

        if ($attempts >= $maxAttempts) {
            return true;
        }

        return false;
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
        $cacheKey = "rate_limit:{$key}";
        $attempts = (int)$this->cache->get($cacheKey, 0);
        $attempts++;

        $this->cache->put($cacheKey, $attempts, $decayMinutes);

        return $attempts;
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
        $cacheKey = "rate_limit:{$key}";
        $attempts = (int)$this->cache->get($cacheKey, 0);

        return max(0, $maxAttempts - $attempts);
    }

    /**
     * Clear the rate limit for a key
     * 
     * @param string $key The rate limit key
     * @return void
     */
    public function reset(string $key): void
    {
        $cacheKey = "rate_limit:{$key}";
        $this->cache->forget($cacheKey);
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
        $cacheKey = "rate_limit:{$key}";
        $attempts = (int)$this->cache->get($cacheKey, 0);

        if ($attempts === 0) {
            return 0;
        }

        // This is approximate since cache expiration isn't tracked precisely
        return $decayMinutes * 60;
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
}
