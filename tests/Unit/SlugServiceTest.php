<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\SlugService;
use App\Models\Tag;

class SlugServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_unique_slug(): void
    {
        $svc = new SlugService();

        Tag::query()->create(['name' => 'Hello World', 'slug' => 'hello-world']);

        $slug = $svc->uniqueSlug('Hello World', Tag::class);
        $this->assertNotEquals('hello-world', $slug);
        $this->assertStringStartsWith('hello-world-', $slug);
    }
}
