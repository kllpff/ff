<?php

namespace FF\Http\Middleware;

use Closure;
use FF\Http\Request;
use FF\Http\Response;

/**
 * SecurityHeadersMiddleware - Adds common security-related HTTP headers.
 */
class SecurityHeadersMiddleware implements MiddlewareInterface
{
    /**
     * Default set of headers to append when not already present.
     *
     * @var array<string,string>
     */
    protected array $headers = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
        'Referrer-Policy' => 'no-referrer-when-downgrade',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
        'X-Permitted-Cross-Domain-Policies' => 'none',
        'X-XSS-Protection' => '0',
    ];

    /**
     * Content Security Policy definition.
     *
     * @var string|null
     */
    protected ?string $contentSecurityPolicy = "default-src 'self'; img-src 'self' data:; script-src 'self'; style-src 'self' 'unsafe-inline'";

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!($response instanceof Response)) {
            $response = new Response((string)$response);
        }

        $existing = $response->getHeaders();

        foreach ($this->headers as $name => $value) {
            if (!array_key_exists($name, $existing)) {
                $response->header($name, $value);
            }
        }

        if ($this->contentSecurityPolicy && !array_key_exists('Content-Security-Policy', $existing)) {
            $response->header('Content-Security-Policy', $this->contentSecurityPolicy);
        }

        if ($this->shouldAddStrictTransportSecurity()) {
            $response->header('Strict-Transport-Security', 'max-age=63072000; includeSubDomains; preload');
        }

        return $response;
    }

    /**
     * Determine if HSTS header should be added.
     */
    protected function shouldAddStrictTransportSecurity(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;
        return $forwardedProto === 'https';
    }
}
