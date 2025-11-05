<?php

namespace FF\Framework\Http\Middleware;

use FF\Framework\Http\Request;
use FF\Framework\Http\Response;
use Closure;

/**
 * HttpsRedirectMiddleware - Redirect HTTP to HTTPS
 */
class HttpsRedirectMiddleware
{
    protected array $except = [];

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldRedirect($request)) {
            $url = 'https://' . $request->header('Host') . $request->getPath();
            return response()->redirect($url);
        }

        return $next($request);
    }

    protected function shouldRedirect(Request $request): bool
    {
        if ($request->isSecure()) {
            return false;
        }

        $path = $request->getPath();

        foreach ($this->except as $exception) {
            if (strpos($path, $exception) === 0) {
                return false;
            }
        }

        return true;
    }
}
