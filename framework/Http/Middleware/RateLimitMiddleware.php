<?php

namespace FF\Framework\Http\Middleware;

use FF\Framework\Http\Request;
use FF\Framework\Http\Response;
use FF\Framework\Security\RateLimiter;
use FF\Framework\Cache\Cache;
use Closure;

/**
 * RateLimitMiddleware - Rate Limiting Middleware
 * 
 * Limits the number of requests from a client within a time window.
 * Can be applied to specific routes or route groups.
 */
class RateLimitMiddleware implements MiddlewareInterface
{
    /**
     * Default max attempts
     */
    protected int $maxAttempts = 60;

    /**
     * Default decay window in minutes
     */
    protected int $decayMinutes = 1;

    /**
     * Create a new middleware instance
     * 
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $decayMinutes Decay window in minutes
     */
    public function __construct(int $maxAttempts = 60, int $decayMinutes = 1)
    {
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }

    /**
     * Handle an incoming request
     * 
     * @param Request $request The incoming request
     * @param Closure $next The next middleware
     * @return mixed The response
     */
    public function handle(Request $request, Closure $next)
    {
        $limiter = new RateLimiter(new Cache());
        $identifier = $_SERVER['REMOTE_ADDR']; // Use IP address

        // Check if rate limited
        if ($limiter->isLimited($identifier, $this->maxAttempts, $this->decayMinutes)) {
            // Return 429 Too Many Requests
            return new Response(
                json_encode([
                    'error' => 'Too many requests',
                    'message' => "You have exceeded the rate limit. Max {$this->maxAttempts} requests per {$this->decayMinutes} minute(s).",
                    'retry_after' => $limiter->getRetryAfter($identifier, $this->decayMinutes),
                ]),
                429,
                ['Content-Type' => 'application/json']
            );
        }

        // Record this attempt
        $limiter->recordAttempt($identifier, $this->decayMinutes);

        // Continue to next middleware
        return $next($request);
    }
}
