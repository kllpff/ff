<?php

namespace App\Middleware;

use Closure;
use FF\Http\Request;
use FF\Http\Response;
use FF\Http\Middleware\MiddlewareInterface;

class ApiAuthMiddleware implements MiddlewareInterface
{
    protected string $token;

    public function __construct(?string $token = null)
    {
        $env = $_ENV['API_TOKEN'] ?? null;
        $this->token = $token ?? ($env ?? env('API_TOKEN', ''));
    }

    public function handle(Request $request, Closure $next)
    {
        $provided = trim((string)$request->header('x-api-key', ''));

        if ($this->token === '' || $provided === '' || !hash_equals($this->token, $provided)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Missing or invalid API key.',
            ], 401);
        }

        return $next($request);
    }
}
