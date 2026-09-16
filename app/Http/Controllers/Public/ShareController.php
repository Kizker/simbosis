<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Share;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShareController extends Controller
{
    private const CHANNELS = ['wa', 'fb', 'x', 'telegram'];

    public function store(Request $request, Article $article)
    {
        abort_unless($article->status?->value === 'published', 404);

        $data = $request->validate(['channel' => ['required', 'in:'.implode(',', self::CHANNELS)]]);

        $this->trackShare($request, $article, $data['channel']);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->away($this->buildShareUrl($data['channel'], $article));
    }

    public function redirect(Request $request, Article $article, string $channel)
    {
        abort_unless($article->status?->value === 'published', 404);
        abort_unless(in_array($channel, self::CHANNELS, true), 404);

        $this->trackShare($request, $article, $channel);

        return redirect()->away($this->buildShareUrl($channel, $article));
    }

    private function trackShare(Request $request, Article $article, string $channel): void
    {
        DB::transaction(function () use ($request, $article, $channel) {
            Share::query()->create([
                'article_id' => $article->id,
                'channel' => $channel,
                'ip_hash' => hash('sha256', (string) $request->ip()),
                'user_agent_hash' => hash('sha256', substr((string) $request->userAgent(), 0, 255)),
            ]);
            $article->increment('share_count');
        });
    }

    private function buildShareUrl(string $channel, Article $article): string
    {
        $canonicalUrl = $article->canonical_url ?: route('article.show', $article);
        $title = $article->title;

        return match ($channel) {
            'wa' => 'https://wa.me/?text='.urlencode($title.' '.$canonicalUrl),
            'fb' => 'https://www.facebook.com/sharer/sharer.php?u='.urlencode($canonicalUrl),
            'x' => 'https://twitter.com/intent/tweet?text='.urlencode($title).'&url='.urlencode($canonicalUrl),
            'telegram' => 'https://t.me/share/url?url='.urlencode($canonicalUrl).'&text='.urlencode($title),
            default => $canonicalUrl,
        };
    }
}
