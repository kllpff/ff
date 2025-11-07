<?php

namespace FF\Http\Middleware;

use FF\Http\Request;
use FF\Session\SessionManager;
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
        $session = $this->sessionManager();

        if ($this->isAuthenticated($session)) {
            return $next($request);
        }

        if ($this->expectsJson($request)) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Remember intended destination
        $session->set('intended_url', $request->getUri());
        $session->flash('error', 'Please log in to continue.');

        return redirect('/login');
    }

    /**
     * Determine if the session has an authenticated user.
     */
    protected function isAuthenticated(SessionManager $session): bool
    {
        return $session->has('auth_user_id');
    }

    /**
     * Determine if the request expects a JSON response.
     */
    protected function expectsJson(Request $request): bool
    {
        $accept = strtolower((string)$request->header('accept', ''));
        $requestedWith = strtolower((string)$request->header('x-requested-with', ''));

        return str_contains($accept, 'json') || $requestedWith === 'xmlhttprequest';
    }

    /**
     * Resolve the session manager from the container and ensure it's started.
     */
    protected function sessionManager(): SessionManager
    {
        /** @var SessionManager $session */
        $session = session();
        if (!$session->isStarted()) {
            $session->start();
        }

        return $session;
    }
}
