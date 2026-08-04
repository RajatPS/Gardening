<?php

namespace Tests\Feature;

use Tests\TestCase;

class GoogleLocalhostConfigTest extends TestCase
{
    public function test_google_redirect_uses_localhost_callback_url(): void
    {
        $this->assertSame(
            'http://localhost:8000/auth/google/callback',
            config('services.google.redirect')
        );
    }
}
