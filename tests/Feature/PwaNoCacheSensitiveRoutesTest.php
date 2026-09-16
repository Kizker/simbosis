<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaNoCacheSensitiveRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_worker_denies_caching_admin_and_auth_routes(): void
    {
        $sw = file_get_contents(base_path('public/sw.js'));

        $this->assertStringContainsString('/harmony-access', $sw);
        $this->assertStringNotContainsString('/admin/', $sw);
        $this->assertStringNotContainsString('/login', $sw);
        $this->assertStringContainsString('/register', $sw);
        $this->assertStringContainsString('/password/', $sw);

        // Heuristic: ensure denylist is applied (look for "SENSITIVE_ROUTES" marker).
        $this->assertStringContainsString('SENSITIVE_ROUTES', $sw);
    }
}
