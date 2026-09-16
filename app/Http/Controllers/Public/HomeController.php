<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\AdsBanner;
use App\Services\TrendingService;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(TrendingService $trending)
    {
        $payload = Cache::remember('public:home', now()->addMinutes(5), function () {
            $breaking = Article::query()->breaking()->with(['category','author'])->latest('published_at')->take(6)->get();
            $latest = Article::query()->published()->with(['category','author'])->latest('published_at')->take(12)->get();
            $popular = Article::query()->published()->with(['category','author'])->orderByDesc('view_count')->take(10)->get();
            $featured = Article::query()->featured()->with(['category','author'])->latest('published_at')->take(6)->get();
            $heroSlides = Article::query()
                ->published()
                ->with(['category','author'])
                ->whereNotNull('cover_image_path')
                ->where('cover_image_path', '!=', '')
                ->latest('published_at')
                ->take(8)
                ->get();

            $trendingTags = Tag::query()
                ->whereHas('articles', fn($query) => $query->published())
                ->withCount([
                    'articles as published_articles_count' => fn($query) => $query->published(),
                ])
                ->orderByDesc('published_articles_count')
                ->take(10)
                ->get();

            $homeCategoryColumns = Category::query()
                ->where('is_active', true)
                ->where('slug', '!=', 'otomotif')
                ->whereHas('articles', fn($q) => $q->published())
                ->withCount([
                    'articles as published_articles_count' => fn($q) => $q->published(),
                ])
                ->orderByDesc('published_articles_count')
                ->take(8)
                ->get()
                ->map(function ($category) {
                    $category->setRelation('home_articles', Article::query()
                        ->published()
                        ->with(['category', 'author'])
                        ->where('category_id', $category->id)
                        ->latest('published_at')
                        ->take(5)
                        ->get());

                    return $category;
                })
                ->filter(fn($category) => $category->home_articles->isNotEmpty())
                ->values();

            $ads = AdsBanner::query()->where('is_active', true)->get()->groupBy('slot');

            return compact('breaking','latest','popular','featured','heroSlides','trendingTags','homeCategoryColumns','ads');
        });

        $payload['trendingArticles'] = Cache::remember('public:trending:home', now()->addMinutes(5), fn() => $trending->getTrending(8));

        return Inertia::render('Public/Home', $payload);
    }
}
