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
     * Set to null to disable CSP entirely.
     *
     * По умолчанию - безопасная конфигурация для production.
     * Для development можно отключить через конфиг.
     *
     * @var string|null
     */
    protected ?string $contentSecurityPolicy = "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'";

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

        // CSP можно отключить через конфиг для development
        $cspEnabled = config('app.enable_csp', true);
        $csp = $cspEnabled ? $this->contentSecurityPolicy : null;
        
        if ($csp && !array_key_exists('Content-Security-Policy', $existing)) {
            $response->header('Content-Security-Policy', $csp);
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

        // Only trust X-Forwarded-Proto from trusted proxies
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
        $trustedProxies = config('app.trusted_proxies', []);

        $isTrustedProxy = false;
        if ($trustedProxies === '*') {
            $isTrustedProxy = true;
        } elseif (is_array($trustedProxies) && in_array($remoteAddr, $trustedProxies, true)) {
            $isTrustedProxy = true;
        }

        if ($isTrustedProxy) {
            $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;
            return $forwardedProto === 'https';
        }

        return false;
    }
}
