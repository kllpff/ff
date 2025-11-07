<?php

namespace FF\Http\Middleware;

use Closure;
use FF\Http\Request;
use FF\Http\Response;

/**
 * CorrelationIdMiddleware - Attaches a per-request correlation ID.
 *
 * - Accepts incoming X-Request-ID or X-Correlation-ID headers (sanitized)
 * - Generates a new ID when missing/invalid
 * - Exposes the ID via container binding ('request_id' and 'correlation_id')
 * - Adds the ID to the response header 'X-Request-ID'
 */
class CorrelationIdMiddleware implements MiddlewareInterface
{
    /**
     * Handle incoming request and attach correlation ID.
     */
    public function handle(Request $request, Closure $next)
    {
        $id = $this->extractOrGenerateId($request);

        // Bind into container for global access via helper
        try {
            \app()->singleton('request_id', $id);
            \app()->singleton('correlation_id', $id);
        } catch (\Throwable $e) {
            // Non-fatal: continue without container binding
        }

        $response = $next($request);

        if (!($response instanceof Response)) {
            $response = new Response((string)$response);
        }

        // Always return the ID to the client for tracing
        $response->header('X-Request-ID', $id);

        return $response;
    }

    /**
     * Extract ID from headers (sanitized) or generate a new one.
     */
    protected function extractOrGenerateId(Request $request): string
    {
        $incoming = $request->header('X-Request-ID') ?? $request->header('X-Correlation-ID');

        if (is_string($incoming)) {
            $incoming = trim($incoming);
            if ($this->isValidId($incoming)) {
                return $incoming;
            }
        }

        return $this->generateId();
    }

    /**
     * Very conservative ID validation.
     * Allows alphanumerics plus '-', '_', '.'. Length 8..64.
     */
    protected function isValidId(string $id): bool
    {
        return (bool)preg_match('/^[A-Za-z0-9][A-Za-z0-9\-_.]{7,63}$/', $id);
    }

    /**
     * Generate a random 32-char hex ID.
     */
    protected function generateId(): string
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (\Throwable $e) {
            return substr(hash('sha256', uniqid('', true)), 0, 32);
        }
    }
}