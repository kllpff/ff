<?php

namespace FF\Http\Middleware;

use FF\Http\Request;
use FF\Http\Response;
use FF\Security\CsrfGuard;
use Closure;

/**
 * CsrfMiddleware - CSRF Protection Middleware
 * 
 * Validates CSRF tokens on state-changing requests (POST, PUT, DELETE).
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
        $this->ensureSessionStarted();

        /** @var CsrfGuard $guard */
        $guard = app(CsrfGuard::class);

        // Always ensure a token exists for subsequent requests
        $guard->token();

        if ($this->shouldBypass($request)) {
            $response = $next($request);
            $this->attachTokenHeader($response, $guard);
            return $response;
        }

        if (!$guard->validate($request)) {
            return $this->invalidTokenResponse($request);
        }

        $response = $next($request);
        $this->attachTokenHeader($response, $guard);

        return $response;
    }

    /**
     * Determine if the request should bypass CSRF verification.
     */
    protected function shouldBypass(Request $request): bool
    {
        $method = strtoupper($request->getMethod());
        $safeMethods = ['GET', 'HEAD', 'OPTIONS', 'TRACE'];

        if (in_array($method, $safeMethods, true)) {
            return true;
        }

        $uri = trim($request->getUri(), '/');

        foreach ($this->except as $except) {
            if ($this->matches($except, $uri)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Simple pattern matcher supporting * wildcards.
     */
    protected function matches(string $pattern, string $uri): bool
    {
        if ($pattern === '/') {
            return $uri === '';
        }

        $pattern = trim($pattern, '/');

        if ($pattern === $uri) {
            return true;
        }

        $regex = '#^' . str_replace('\*', '.*', preg_quote($pattern, '#')) . '$#i';
        return (bool)preg_match($regex, $uri);
    }

    /**
     * Attach the fresh CSRF token to the response headers.
     */
    protected function attachTokenHeader(Response $response, CsrfGuard $guard): void
    {
        $response->header(CsrfGuard::getHeaderName(), $guard->token());
    }

    /**
     * Build an invalid CSRF token response.
     */
    protected function invalidTokenResponse(Request $request): Response
    {
        // Attempt to provide a user-friendly message for HTML responses
        if (function_exists('session')) {
            session()->flash('error', 'Security verification failed. Please refresh the page and try again.');
        }

        $token = csrf_token();
        $accept = strtolower((string)$request->header('accept', ''));
        if (str_contains($accept, 'json') || $request->header('x-requested-with') === 'XMLHttpRequest') {
            $response = response()->json(['message' => 'Invalid CSRF token'], 419);
            $response->header(CsrfGuard::getHeaderName(), $token);
            return $response;
        }

        $response = response('Invalid CSRF token', 419, [
            'Content-Type' => 'text/plain; charset=UTF-8'
        ]);
        $response->header(CsrfGuard::getHeaderName(), $token);
        return $response;
    }

    /**
     * Ensure the PHP session is started.
     */
    protected function ensureSessionStarted(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }
}
