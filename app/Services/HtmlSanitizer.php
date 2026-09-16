<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\Str;

class HtmlSanitizer
{
    public function sanitize(string $html): string
    {
        $allowTags = config('security.html_allowlist.tags', []);
        $allowAttrs = config('security.html_allowlist.attrs', []);
        $allowProtocols = config('security.html_allowlist.protocols', ['http','https']);

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $root = $doc->documentElement;
        $this->sanitizeNode($root, $allowTags, $allowAttrs, $allowProtocols);

        $inner = '';
        foreach ($root->childNodes as $child) $inner .= $doc->saveHTML($child);

        libxml_clear_errors();
        return trim($inner);
    }

    private function sanitizeNode(DOMNode $node, array $allowTags, array $allowAttrs, array $allowProtocols): void
    {
        if ($node instanceof DOMElement) {
            $tag = strtolower($node->tagName);

            if (in_array($tag, ['script', 'style', 'noscript'], true)) {
                $this->removeNode($node);
                return;
            }

            if ($tag !== 'div' && !in_array($tag, $allowTags, true)) {
                $this->unwrap($node);
                return;
            }

            $attrsAllowedForTag = $allowAttrs[$tag] ?? [];
            $attrsAllowedGlobal = $allowAttrs['*'] ?? [];
            $allowed = array_unique(array_merge($attrsAllowedForTag, $attrsAllowedGlobal));

            $toRemove = [];
            foreach (iterator_to_array($node->attributes ?? []) as $attr) {
                $name = strtolower($attr->name);
                if (!in_array($name, $allowed, true)) { $toRemove[] = $name; continue; }

                $value = (string) $attr->value;

                if (in_array($name, ['href','src'], true)) {
                    $parsed = parse_url(trim($value));
                    $scheme = strtolower($parsed['scheme'] ?? '');
                    if ($scheme && !in_array($scheme, $allowProtocols, true)) { $toRemove[] = $name; continue; }
                }

                if ($tag === 'a' && $name === 'href') {
                    if (!Str::startsWith($value, ['http://','https://','mailto:','tel:','/','#'])) { $toRemove[] = $name; }
                    if ($node->getAttribute('target') === '_blank') $node->setAttribute('rel', 'noopener noreferrer');
                }

                if ($tag === 'img' && !$node->hasAttribute('loading')) $node->setAttribute('loading', 'lazy');
            }

            foreach ($toRemove as $name) $node->removeAttribute($name);
        }

        $children = [];
        foreach (iterator_to_array($node->childNodes ?? []) as $child) $children[] = $child;
        foreach ($children as $child) $this->sanitizeNode($child, $allowTags, $allowAttrs, $allowProtocols);
    }

    private function unwrap(DOMElement $el): void
    {
        $parent = $el->parentNode;
        if (!$parent) return;
        while ($el->firstChild) $parent->insertBefore($el->firstChild, $el);
        $parent->removeChild($el);
    }

    private function removeNode(DOMElement $el): void
    {
        $parent = $el->parentNode;
        if (!$parent) return;
        $parent->removeChild($el);
    }
}
