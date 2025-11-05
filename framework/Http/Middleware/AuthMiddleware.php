<?php

namespace FF\Framework\Http\Middleware;

use FF\Framework\Http\Request;
use FF\Framework\Http\Response;
use Closure;

/**
 * AuthMiddleware - Authentication Middleware
 * 
 * Ensures user is authenticated before allowing access to protected routes.
 * Will be fully implemented in Stage 5 (Security).
 */
class AuthMiddleware implements MiddlewareInterface
{
    /**
     * Handle an incoming request
     * 
     * @param Request $request The incoming request
     * @param Closure $next The next middleware
     * @return mixed The response
     */
    public function handle(Request $request, Closure $next)
    {
        // Auth check will be implemented in Stage 5
        // For now, just pass through
        return $next($request);
    }
}
