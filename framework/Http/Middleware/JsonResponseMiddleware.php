<?php

namespace FF\Framework\Http\Middleware;

use FF\Framework\Http\Request;
use FF\Framework\Http\Response;
use Closure;

/**
 * JsonResponseMiddleware - Auto-wrap responses in JSON when appropriate
 */
class JsonResponseMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->wantsJson() && !$this->isJson($response)) {
            $response->header('Content-Type', 'application/json');
        }

        return $response;
    }

    protected function isJson(Response $response): bool
    {
        $contentType = $response->getHeader('Content-Type') ?? '';
        return strpos($contentType, 'application/json') !== false;
    }
}
