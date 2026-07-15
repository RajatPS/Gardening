<?php

namespace Tests\Feature;

use App\Http\Middleware\SetDynamicRootUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AssetUrlGenerationTest extends TestCase
{
    public function test_asset_urls_follow_the_current_request_host(): void
    {
        config(['app.url' => 'https://stale.example']);

        $request = Request::create('https://demo.local/asset-url-test', 'GET', [], [], [], [
            'HTTPS' => 'on',
            'HTTP_HOST' => 'demo.local',
        ]);

        $middleware = new SetDynamicRootUrl();
        $response = $middleware->handle($request, function () {
            return response()->json([
                'root' => URL::to('/'),
                'asset' => asset('build/app.css'),
            ]);
        });

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('https://demo.local', $response->getData(true)['root']);
        $this->assertSame('https://demo.local/build/app.css', $response->getData(true)['asset']);
    }

    public function test_asset_urls_use_forwarded_headers_when_present(): void
    {
        config(['app.url' => 'https://stale.example']);

        $request = Request::create('https://internal.local/asset-url-test', 'GET', [], [], [], [
            'HTTPS' => 'on',
            'HTTP_HOST' => 'internal.local',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_HOST' => 'my-app.ngrok-free.app',
        ]);

        $middleware = new SetDynamicRootUrl();
        $response = $middleware->handle($request, function () {
            return response()->json([
                'root' => URL::to('/'),
                'asset' => asset('build/app.css'),
            ]);
        });

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('https://my-app.ngrok-free.app', $response->getData(true)['root']);
        $this->assertSame('https://my-app.ngrok-free.app/build/app.css', $response->getData(true)['asset']);
    }
}
