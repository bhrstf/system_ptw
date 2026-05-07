<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Redirect all requests to HTTPS in production.
     *
     * Railway runs behind a proxy, so we also check X-Forwarded-Proto.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('production')) {
            return $next($request);
        }

        $forwardedProto = $request->headers->get('X-Forwarded-Proto');
        $isHttps = false;

        if (is_string($forwardedProto) && $forwardedProto !== '') {
            $isHttps = str_contains(strtolower($forwardedProto), 'https');
        } else {
            $isHttps = $request->secure();
        }

        if (! $isHttps) {
            $url = 'https://'.$request->getHost().$request->getRequestUri();
            return redirect()->to($url, 301);
        }

        return $next($request);
    }
}

