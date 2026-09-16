<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleViewDaily;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $byStatus = Article::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')->pluck('total','status');

        $views7d = ArticleViewDaily::query()
            ->where('viewed_date', '>=', now()->subDays(7)->toDateString())
            ->select('viewed_date', DB::raw('SUM(count) as total'))
            ->groupBy('viewed_date')
            ->orderBy('viewed_date')
            ->get();

        $topCategories = Category::query()
            ->select('categories.id','categories.name', DB::raw('COUNT(articles.id) as total'))
            ->leftJoin('articles','articles.category_id','=','categories.id')
            ->groupBy('categories.id','categories.name')
            ->orderByDesc('total')->take(8)->get();

        $topTags = Tag::query()
            ->select('tags.id','tags.name', DB::raw('COUNT(article_tag.tag_id) as total'))
            ->leftJoin('article_tag','article_tag.tag_id','=','tags.id')
            ->groupBy('tags.id','tags.name')
            ->orderByDesc('total')->take(8)->get();

        $topAuthors = User::query()
            ->select('users.id','users.name', DB::raw('COUNT(articles.id) as total'))
            ->leftJoin('articles','articles.author_id','=','users.id')
            ->groupBy('users.id','users.name')
            ->orderByDesc('total')->take(8)->get();

        $latestArticles = Article::query()
            ->with(['author:id,name', 'category:id,name'])
            ->latest('updated_at')
            ->take(5)
            ->get(['id', 'title', 'slug', 'status', 'author_id', 'category_id', 'updated_at', 'published_at']);

        $publishedToday = Article::query()
            ->where('status', ArticleStatus::Published)
            ->whereDate('published_at', now()->toDateString())
            ->count();

        $featuredCount = Article::query()->where('is_featured', true)->count();
        $breakingCount = Article::query()->where('is_breaking', true)->count();

        return Inertia::render('Admin/Dashboard', [
            'byStatus' => $byStatus,
            'views7d' => $views7d,
            'topCategories' => $topCategories,
            'topTags' => $topTags,
            'topAuthors' => $topAuthors,
            'latestArticles' => $latestArticles,
            'publishedToday' => $publishedToday,
            'featuredCount' => $featuredCount,
            'breakingCount' => $breakingCount
        ]);
    }
}
