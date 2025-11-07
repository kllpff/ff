<?php

namespace FF\Http\Middleware;

use FF\Http\Request;
use FF\Security\RateLimiter;
use FF\Cache\Cache;
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
     * Shared limiter instance.
     */
    protected RateLimiter $limiter;

    /**
     * Create a new middleware instance.
     * 
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $decayMinutes Decay window in minutes
     * @param RateLimiter|null $limiter Optional shared limiter
     */
    public function __construct(int $maxAttempts = 60, int $decayMinutes = 1, ?RateLimiter $limiter = null)
    {
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
        $this->limiter = $limiter ?? $this->resolveRateLimiter();
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
        $identifier = $this->buildIdentifier($request);

        if ($this->limiter->isLimited($identifier, $this->maxAttempts, $this->decayMinutes)) {
            return response()->json([
                'error' => 'Too many requests',
                'message' => "You have exceeded the rate limit. Max {$this->maxAttempts} requests per {$this->decayMinutes} minute(s).",
                'retry_after' => $this->limiter->getRetryAfter($identifier, $this->decayMinutes),
            ], 429);
        }

        $this->limiter->recordAttempt($identifier, $this->decayMinutes);

        return $next($request);
    }

    /**
     * Build a scoped rate-limit key.
     */
    protected function buildIdentifier(Request $request): string
    {
        $ip = $request->ip();
        $path = $request->getUri();

        return sprintf('route:%s|ip:%s', $path, $ip);
    }

    /**
     * Resolve shared RateLimiter from the container (fallback to default cache).
     */
    protected function resolveRateLimiter(): RateLimiter
    {
        try {
            if (function_exists('app')) {
                return app(RateLimiter::class);
            }
        } catch (\Throwable $e) {
            // Fall back to local instance below
        }

        return new RateLimiter(new Cache());
    }
}
