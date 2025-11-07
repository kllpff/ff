<?php

namespace FF\Http\Middleware;

use Closure;
use FF\Http\Request;
use FF\Http\Response;

/**
 * RequestSizeLimitMiddleware - Rejects requests exceeding configured size limits.
 */
class RequestSizeLimitMiddleware implements MiddlewareInterface
{
    /**
     * Header that signals request entity too large.
     */
    protected const HEADER_REJECT_REASON = 'X-Request-Rejected';

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $limit = $this->getRequestLimit();

        if ($limit > 0 && $this->calculateRequestSize($request) > $limit) {
            return $this->reject($limit);
        }

        return $next($request);
    }

    /**
     * Determine the configured request size limit in bytes.
     */
    protected function getRequestLimit(): int
    {
        $configValue = config('http.request_size_limit');

        if ($configValue === null) {
            return $this->iniBytes(ini_get('post_max_size')) ?: 0;
        }

        if (is_numeric($configValue)) {
            return (int)$configValue;
        }

        if (is_string($configValue)) {
            return $this->iniBytes($configValue) ?: 0;
        }

        return 0;
    }

    /**
     * Calculate the approximate request size in bytes.
     */
    protected function calculateRequestSize(Request $request): int
    {
        $contentLength = (int)($request->getServer('CONTENT_LENGTH') ?? 0);

        if ($contentLength > 0) {
            return $contentLength;
        }

        // Approximate size by serializing all input
        $inputBytes = strlen(http_build_query($request->all()));

        return $inputBytes;
    }

    /**
     * Convert php.ini shorthand (e.g., 2M) to bytes.
     */
    protected function iniBytes($value): int
    {
        if (!is_string($value)) {
            return (int)$value;
        }

        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $number = (float)$value;
        $suffix = strtolower(substr($value, -1));

        switch ($suffix) {
            case 'g':
                $number *= 1024;
            case 'm':
                $number *= 1024;
            case 'k':
                $number *= 1024;
        }

        return (int)$number;
    }

    /**
     * Build rejection response.
     */
    protected function reject(int $limit): Response
    {
        $response = response()->json([
            'message' => 'Request payload too large.',
            'limit_bytes' => $limit,
        ], 413);
        $response->header(self::HEADER_REJECT_REASON, 'size-limit-exceeded');
        return $response;
    }
}
