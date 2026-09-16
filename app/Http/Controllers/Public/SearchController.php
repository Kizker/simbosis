<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $sort = $request->query('sort', 'newest');

        $query = Article::query()->published()->with(['category', 'author']);

        if ($q !== '') {
            $driver = DB::connection()->getDriverName();
            $useMysqlFullText = $driver === 'mysql' && mb_strlen($q) >= 3;

            if ($useMysqlFullText) {
                $query->whereRaw(
                    'MATCH(title, excerpt, content_html) AGAINST (? IN NATURAL LANGUAGE MODE)',
                    [$q]
                );
            } else {
                $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
                $query->where(function ($sub) use ($like) {
                    $sub->where('title', 'like', $like)
                        ->orWhere('excerpt', 'like', $like)
                        ->orWhere('content_html', 'like', $like);
                });
            }
        }

        if ($sort === 'popular') {
            $query->orderByDesc('view_count');
        } else {
            $query->orderByDesc('published_at');
        }

        $items = $query->paginate(12)->withQueryString();
        $metaTitle = $q !== ''
            ? Str::limit('Hasil pencarian: '.$q, 70)
            : 'Pencarian';
        $metaDescription = $q !== ''
            ? Str::limit('Hasil pencarian untuk "'.$q.'".', 160)
            : 'Cari berita terbaru berdasarkan kata kunci.';

        $heading = 'Hasil Pencarian: "' . $q . '"';
        $breadcrumbs = [
            ['label' => 'Beranda', 'href' => route('home')],
            ['label' => 'Pencarian']
        ];
        $filters = [
            'q' => $q,
            'sort' => $sort
        ];

        return Inertia::render('Public/Listing', compact('heading', 'items', 'filters', 'breadcrumbs', 'metaTitle', 'metaDescription'));
    }

    public function live(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['items' => []]);
        }

        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';

        $items = Article::query()
            ->published()
            ->with('category:id,name,slug')
            ->where(function ($query) use ($like) {
                $query->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like);
            })
            ->orderByDesc('published_at')
            ->limit(6)
            ->get()
            ->map(fn (Article $article) => [
                'title' => $article->title,
                'category' => $article->category?->name,
                'published' => $article->published_at?->diffForHumans(),
                'url' => route('article.show', $article),
            ]);

        return response()->json(['items' => $items]);
    }
}
