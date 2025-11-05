<?php

namespace FF\Framework\Http\Middleware;

use FF\Framework\Http\Request;
use FF\Framework\Http\Response;
use Closure;

/**
 * CorsMiddleware - Cross-Origin Resource Sharing
 */
class CorsMiddleware
{
    protected array $config = [
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization'],
        'max_age' => 3600,
        'credentials' => false,
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('Origin');

        if ($this->isOriginAllowed($origin)) {
            $response = $next($request);

            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Access-Control-Allow-Methods', implode(', ', $this->config['allowed_methods']));
            $response->header('Access-Control-Allow-Headers', implode(', ', $this->config['allowed_headers']));
            $response->header('Access-Control-Max-Age', $this->config['max_age']);

            if ($this->config['credentials']) {
                $response->header('Access-Control-Allow-Credentials', 'true');
            }

            return $response;
        }

        if ($request->isMethod('OPTIONS')) {
            return response('', 204);
        }

        return $next($request);
    }

    protected function isOriginAllowed(?string $origin): bool
    {
        if (!$origin) {
            return false;
        }

        foreach ($this->config['allowed_origins'] as $allowed) {
            if ($allowed === '*' || $allowed === $origin) {
                return true;
            }

            if ($this->patternMatch($allowed, $origin)) {
                return true;
            }
        }

        return false;
    }

    protected function patternMatch(string $pattern, string $origin): bool
    {
        $pattern = preg_quote($pattern, '#');
        $pattern = str_replace('\*', '.*', $pattern);
        return preg_match('#^' . $pattern . '$#', $origin) === 1;
    }
}
