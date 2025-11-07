<?php

namespace FF\Http\Middleware;

use FF\Http\Request;
use FF\Http\Response;
use Closure;

/**
 * CorsMiddleware - Cross-Origin Resource Sharing
 */
class CorsMiddleware
{
    protected array $config = [
        // Strict by default: no origins allowed unless explicitly configured
        'allowed_origins' => [],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization'],
        'exposed_headers' => [],
        'max_age' => 3600,
        'credentials' => false,
        'force_origin_on_credentials' => true,
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('Origin');

        if ($origin && $this->isOriginAllowed($origin)) {
            if ($request->isMethod('OPTIONS')) {
                return $this->addCorsHeaders(response('', 204), $origin);
            }

            return $this->addCorsHeaders($next($request), $origin);
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

        if (empty($this->config['allowed_origins'])) {
            return false;
        }

        foreach ($this->config['allowed_origins'] as $allowed) {
            if ($allowed === '*' || $allowed === $origin) {
                return $this->config['credentials']
                    ? !$this->config['force_origin_on_credentials']
                    : true;
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

    protected function addCorsHeaders(Response $response, string $origin): Response
    {
        if ($this->config['credentials']) {
            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Vary', 'Origin');
            $response->header('Access-Control-Allow-Credentials', 'true');
        } else {
            $response->header('Access-Control-Allow-Origin', $this->resolveAllowedOrigin($origin));
        }

        $response->header('Access-Control-Allow-Methods', implode(', ', $this->config['allowed_methods']));
        $response->header('Access-Control-Allow-Headers', implode(', ', $this->config['allowed_headers']));

        if (!empty($this->config['exposed_headers'])) {
            $response->header('Access-Control-Expose-Headers', implode(', ', $this->config['exposed_headers']));
        }

        $response->header('Access-Control-Max-Age', (string)$this->config['max_age']);

        return $response;
    }

    protected function resolveAllowedOrigin(string $origin): string
    {
        foreach ($this->config['allowed_origins'] as $allowed) {
            if ($allowed === '*') {
                return '*';
            }

            if ($allowed === $origin || $this->patternMatch($allowed, $origin)) {
                return $origin;
            }
        }

        return '*';
    }
}
