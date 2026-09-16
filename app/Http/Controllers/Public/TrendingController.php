<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\TrendingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;

class TrendingController extends Controller
{
    public function index(\Illuminate\Http\Request $request, TrendingService $trending)
    {
        $sort = $request->query('sort', 'latest');
        $perPage = 12;

        if ($sort === 'popular') {
            $articles = Cache::remember(
                'public:trending',
                now()->addMinutes(5),
                fn() => collect($trending->getTrending(24, 48))
            );

            $page = LengthAwarePaginator::resolveCurrentPage();
            $items = new LengthAwarePaginator(
                $articles->forPage($page, $perPage)->values(),
                $articles->count(),
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ]
            );
        } else {
            $items = \App\Models\Article::published()
                ->with(['category', 'author', 'tags'])
                ->orderByDesc('published_at')
                ->paginate($perPage)
                ->withQueryString();
        }

        $heading = 'Semua Berita';
        $breadcrumbs = [
            ['label' => 'Beranda', 'href' => route('home')],
            ['label' => 'Semua Berita']
        ];
        $metaTitle = 'Semua Berita - Simbiosis News';
        $metaDescription = 'Kumpulan semua berita terbaru dan terpopuler dari Simbiosis News.';

        return Inertia::render('Public/Listing', [
            'heading' => $heading,
            'items' => $items,
            'breadcrumbs' => $breadcrumbs,
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
            'filters' => [
                'sort' => $sort,
            ],
        ]);
    }
}
