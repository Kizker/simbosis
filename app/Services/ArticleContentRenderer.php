<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;

class ArticleContentRenderer
{
    public function render(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $root = $doc->documentElement;
        if ($root instanceof DOMElement) {
            $this->replaceMediaEmbeds($doc, $root);
        }

        $inner = '';
        foreach ($root->childNodes as $child) {
            $inner .= $doc->saveHTML($child);
        }

        libxml_clear_errors();

        return trim($inner);
    }

    private function replaceMediaEmbeds(DOMDocument $doc, DOMElement $root): void
    {
        $xpath = new DOMXPath($doc);
        $nodes = $xpath->query('.//figure[contains(concat(" ", normalize-space(@class), " "), " media ")]', $root);
        if (!$nodes) {
            return;
        }

        $figures = [];
        foreach ($nodes as $node) {
            if ($node instanceof DOMElement) {
                $figures[] = $node;
            }
        }

        foreach ($figures as $figure) {
            $url = $this->extractMediaUrl($figure);
            if (!$url) {
                continue;
            }

            $embedUrl = $this->normalizeEmbedUrl($url);
            if (!$embedUrl) {
                continue;
            }

            $wrapper = $doc->createElement('div');
            $wrapper->setAttribute('class', 'article-embed');

            $iframe = $doc->createElement('iframe');
            $iframe->setAttribute('class', 'article-embed-frame');
            $iframe->setAttribute('src', $embedUrl);
            $iframe->setAttribute('title', 'Embedded video');
            $iframe->setAttribute('loading', 'lazy');
            $iframe->setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
            $iframe->setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
            $iframe->setAttribute('allowfullscreen', 'allowfullscreen');

            $wrapper->appendChild($iframe);
            $figure->parentNode?->replaceChild($wrapper, $figure);
        }
    }

    private function extractMediaUrl(DOMElement $figure): ?string
    {
        foreach ($figure->getElementsByTagName('oembed') as $node) {
            if ($node instanceof DOMElement && $node->hasAttribute('url')) {
                return trim((string) $node->getAttribute('url'));
            }
        }

        foreach ($figure->getElementsByTagName('div') as $node) {
            if ($node instanceof DOMElement && $node->hasAttribute('data-oembed-url')) {
                return trim((string) $node->getAttribute('data-oembed-url'));
            }
        }

        foreach ($figure->getElementsByTagName('iframe') as $node) {
            if ($node instanceof DOMElement && $node->hasAttribute('src')) {
                return trim((string) $node->getAttribute('src'));
            }
        }

        return null;
    }

    private function normalizeEmbedUrl(string $url): ?string
    {
        $parts = parse_url($url);
        if (!is_array($parts)) {
            return null;
        }

        $host = strtolower($parts['host'] ?? '');
        $host = preg_replace('/^www\./', '', $host) ?? $host;
        $path = trim((string) ($parts['path'] ?? ''), '/');
        parse_str((string) ($parts['query'] ?? ''), $query);

        if (in_array($host, ['youtube.com', 'm.youtube.com', 'youtu.be', 'youtube-nocookie.com'], true)) {
            $videoId = $query['v'] ?? null;

            if (!$videoId && $path !== '') {
                $segments = array_values(array_filter(explode('/', $path)));

                if ($host === 'youtu.be') {
                    $videoId = $segments[0] ?? null;
                } elseif (($segments[0] ?? null) === 'embed') {
                    $videoId = $segments[1] ?? null;
                } elseif (in_array($segments[0] ?? null, ['shorts', 'live'], true)) {
                    $videoId = $segments[1] ?? null;
                }
            }

            if ($videoId) {
                return 'https://www.youtube-nocookie.com/embed/'.rawurlencode($videoId);
            }
        }

        if (in_array($host, ['vimeo.com', 'player.vimeo.com'], true)) {
            if (preg_match('#(?:video/)?([0-9]+)$#', $path, $matches)) {
                return 'https://player.vimeo.com/video/'.rawurlencode($matches[1]);
            }
        }

        return null;
    }
}
