<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FulltextSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_relevant_results(): void
    {
        $cat = Category::create(['name' => 'Umum', 'slug' => 'umum']);
        $author = User::create(['name'=>'User','email'=>uniqid().'@example.com','password'=>bcrypt('Password123!')]);

        Article::create([
            'title' => 'Berita Teknologi AI',
            'slug' => 'berita-teknologi-ai',
            'excerpt' => 'Membahas AI dan tren terbaru.',
            'content_html' => '<p>AI berkembang cepat</p>',
            'author_id' => $author->id,
            'category_id' => $cat->id,
            'status' => ArticleStatus::Published->value,
            'published_at' => now(),
            'reading_time' => 1,
            'view_count' => 10,
            'share_count' => 2,
            'is_breaking' => false,
            'is_featured' => false,
            'seo_title' => 'SEO',
            'seo_desc' => 'SEO',
        ]);

        Article::create([
            'title' => 'Olahraga Sepak Bola',
            'slug' => 'olahraga-sepak-bola',
            'excerpt' => 'Pertandingan semalam.',
            'content_html' => '<p>Bola</p>',
            'author_id' => $author->id,
            'category_id' => $cat->id,
            'status' => ArticleStatus::Published->value,
            'published_at' => now(),
            'reading_time' => 1,
            'view_count' => 5,
            'share_count' => 1,
            'is_breaking' => false,
            'is_featured' => false,
            'seo_title' => 'SEO',
            'seo_desc' => 'SEO',
        ]);

        $resp = $this->get('/search?q=AI');
        $resp->assertStatus(200);
        $resp->assertSee('Berita Teknologi AI');
        $resp->assertDontSee('Olahraga Sepak Bola');
    }
}
