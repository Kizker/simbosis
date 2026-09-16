<?php

namespace Tests\Unit;

use App\Services\ArticleContentRenderer;
use Tests\TestCase;

class ArticleContentRendererTest extends TestCase
{
    public function test_it_renders_youtube_media_embeds_into_iframes(): void
    {
        $html = '<p>Intro</p><figure class="media"><oembed url="https://www.youtube.com/watch?v=dQw4w9WgXcQ"></oembed></figure>';

        $rendered = app(ArticleContentRenderer::class)->render($html);

        $this->assertStringContainsString('<p>Intro</p>', $rendered);
        $this->assertStringContainsString('class="article-embed"', $rendered);
        $this->assertStringContainsString('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $rendered);
        $this->assertStringContainsString('allowfullscreen', $rendered);
        $this->assertStringNotContainsString('<oembed', $rendered);
    }

    public function test_it_keeps_unknown_media_urls_as_original_markup(): void
    {
        $html = '<figure class="media"><oembed url="https://example.com/video/123"></oembed></figure>';

        $rendered = app(ArticleContentRenderer::class)->render($html);

        $this->assertSame($html, $rendered);
    }
}
