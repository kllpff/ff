<?php

namespace FF\Http\Middleware;

use FF\Http\Request;
use Closure;

/**
 * MiddlewareInterface - HTTP Middleware Interface
 * 
 * Defines the contract for HTTP middleware implementations.
 * Middleware can inspect and transform HTTP requests and responses.
 */
interface MiddlewareInterface
{
    /**
     * Handle an incoming request
     * 
     * @param Request $request The incoming request
     * @param Closure $next The next middleware/handler in the pipeline
     * @return mixed The response
     */
    public function handle(Request $request, Closure $next);
}
