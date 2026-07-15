<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class SetDynamicRootUrl
{
    public function handle(Request $request, Closure $next)
    {
        $request->setTrustedProxies(['*'], Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);

        $rootUrl = $this->resolveRootUrl($request);

        config(['app.url' => $rootUrl]);
        URL::forceRootUrl($rootUrl);
        URL::forceScheme($this->resolveScheme($request));

        return $next($request);
    }

    protected function resolveRootUrl(Request $request): string
    {
        $host = $this->extractForwardedHost($request) ?? $request->getHost();
        $scheme = $this->resolveScheme($request);
        $port = $request->getPort();

        if ($host === '') {
            return $request->getSchemeAndHttpHost();
        }

        if ($port !== null && $port !== 80 && $port !== 443) {
            return $scheme.'://'.$host.':'.$port;
        }

        return $scheme.'://'.$host;
    }

    protected function resolveScheme(Request $request): string
    {
        $forwardedProto = $this->extractForwardedValue($request, 'X-Forwarded-Proto');

        if ($forwardedProto !== null) {
            return explode(',', $forwardedProto)[0];
        }

        return $request->getScheme();
    }

    protected function extractForwardedHost(Request $request): ?string
    {
        $host = $this->extractForwardedValue($request, 'X-Forwarded-Host');

        if ($host !== null) {
            return trim(explode(',', $host)[0]);
        }

        return null;
    }

    protected function extractForwardedValue(Request $request, string $header): ?string
    {
        $value = $request->headers->get($header);

        if ($value === null) {
            return null;
        }

        return trim($value);
    }
}
