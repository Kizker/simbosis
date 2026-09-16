<?php

namespace Tests\Unit;

use App\Services\HtmlSanitizer;
use Tests\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_it_keeps_editor_markup_and_strips_unsafe_content(): void
    {
        $html = <<<'HTML'
<h2>Judul</h2>
<p>Paragraf <strong>penting</strong> dengan <span class="text-big">font besar</span> dan <a href="https://example.com" target="_blank">tautan</a>.</p>
<ol type="A" start="3" reversed>
  <li>Poin</li>
</ol>
<figure class="image image-style-block">
  <img src="https://example.com/image.jpg" alt="Contoh">
  <figcaption>Keterangan</figcaption>
</figure>
<figure class="media"><oembed url="https://www.youtube.com/watch?v=dQw4w9WgXcQ"></oembed></figure>
<script>alert('xss')</script>
HTML;

        $sanitized = app(HtmlSanitizer::class)->sanitize($html);

        $this->assertStringContainsString('<h2>Judul</h2>', $sanitized);
        $this->assertStringContainsString('<strong>penting</strong>', $sanitized);
        $this->assertStringContainsString('<span class="text-big">font besar</span>', $sanitized);
        $this->assertStringContainsString('target="_blank"', $sanitized);
        $this->assertStringContainsString('rel="noopener noreferrer"', $sanitized);
        $this->assertStringContainsString('<ol type="A" start="3" reversed>', $sanitized);
        $this->assertStringContainsString('<figure class="image image-style-block">', $sanitized);
        $this->assertStringContainsString('loading="lazy"', $sanitized);
        $this->assertStringContainsString('<figcaption>Keterangan</figcaption>', $sanitized);
        $this->assertStringContainsString('<figure class="media"><oembed url="https://www.youtube.com/watch?v=dQw4w9WgXcQ"></oembed></figure>', $sanitized);
        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringNotContainsString('alert(', $sanitized);
    }
}
