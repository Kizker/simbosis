<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Collection;

class TrendingService
{
    /**
     * Deterministic:
     * raw = view_count + 3*share_count
     * decay = exp(-hours_since_publish / half_life_hours)
     * score = raw * decay
     */
    public function getTrending(int $limit=12, int $halfLifeHours=48): Collection
    {
        $now = now();

        return Article::query()->published()->get()->map(function (Article $a) use ($now,$halfLifeHours) {
            $publishedAt = $a->published_at ?? $now;
            $hours = max(0.0, $publishedAt->diffInMinutes($now)/60.0);
            $raw = ((float)$a->view_count) + (3.0 * (float)$a->share_count);
            $decay = exp(-$hours / max(1, $halfLifeHours));
            $a->trending_score = $raw * $decay;
            return $a;
        })->sortByDesc('trending_score')->take($limit)->values();
    }
}
