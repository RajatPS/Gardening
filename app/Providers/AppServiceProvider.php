<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $this->app->booted(function () {
            /** @var Request $request */
            $request = $this->app['request'];

            URL::forceRootUrl($this->resolveRootUrl($request));
        });
    }

    protected function resolveRootUrl(Request $request): string
    {
        $forwardedHost = $request->headers->get('X-Forwarded-Host');
        $forwardedProto = $request->headers->get('X-Forwarded-Proto');

        $host = $forwardedHost ? explode(',', $forwardedHost)[0] : $request->getHost();
        $scheme = $forwardedProto ? explode(',', $forwardedProto)[0] : $request->getScheme();

        if ($host === '') {
            return $request->getSchemeAndHttpHost();
        }

        $port = $request->getPort();
        $normalizedHost = trim($host);

        if ($port !== null && $port !== 80 && $port !== 443) {
            return $scheme.'://'.$normalizedHost.':'.$port;
        }

        return $scheme.'://'.$normalizedHost;
    }
}
