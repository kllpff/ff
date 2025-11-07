<?php

namespace FF\Http\Middleware;

use FF\Http\Request;
use FF\Http\Response;
use Closure;

/**
 * CompressionMiddleware - GZip compression for responses
 */
class CompressionMiddleware
{
    protected int $minLength = 1024;
    protected array $compressibleTypes = [
        'application/json',
        'application/xml',
        'text/html',
        'text/plain',
        'text/css',
        'application/javascript',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$this->shouldCompress($request, $response)) {
            return $response;
        }

        $content = $response->getContent();

        if (strlen($content) < $this->minLength) {
            return $response;
        }

        $compressed = gzencode($content, 9);

        if ($compressed === false) {
            return $response;
        }

        $response->setContent($compressed);
        $response->header('Content-Encoding', 'gzip');
        $response->header('Vary', 'Accept-Encoding');

        return $response;
    }

    protected function shouldCompress(Request $request, Response $response): bool
    {
        $acceptEncoding = $request->header('Accept-Encoding');

        if (!$acceptEncoding || strpos($acceptEncoding, 'gzip') === false) {
            return false;
        }

        $contentType = $response->getHeader('Content-Type');

        if (!$contentType) {
            return false;
        }

        foreach ($this->compressibleTypes as $type) {
            if (strpos($contentType, $type) === 0) {
                return true;
            }
        }

        return false;
    }
}
