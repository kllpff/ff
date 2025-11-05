<?php

namespace FF\Framework\Http\Middleware;

use FF\Framework\Http\Request;
use Closure;

/**
 * CsrfMiddleware - CSRF Protection Middleware
 * 
 * Validates CSRF tokens on state-changing requests (POST, PUT, DELETE).
 * Will be fully implemented in Stage 5 (Security).
 */
class CsrfMiddleware implements MiddlewareInterface
{
    /**
     * Excluded URIs from CSRF check
     * 
     * @var array
     */
    protected array $except = [];

    /**
     * Handle an incoming request
     * 
     * @param Request $request The incoming request
     * @param Closure $next The next middleware
     * @return mixed The response
     */
    public function handle(Request $request, Closure $next)
    {
        // CSRF validation will be implemented in Stage 5
        // For now, just pass through
        return $next($request);
    }
}
