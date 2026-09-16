<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TagController extends Controller
{
    public function show(Request $request, Tag $tag)
    {
        $sort = $request->query('sort', 'latest');
        $query = $tag->articles()
            ->published()
            ->with(['category','author','tags']);

        if ($sort === 'popular') {
            $query->orderByDesc('view_count');
        } else {
            $query->orderByDesc('published_at');
        }

        $items = $query->paginate(12)->withQueryString();
        $heading = 'Tag: '.$tag->name;
        $subheading = 'Kumpulan berita dengan tag #'.$tag->name.'.';
        $breadcrumbs = [
            ['label' => 'Beranda', 'href' => route('home')],
            ['label' => 'Tag'],
            ['label' => $tag->name],
        ];
        $metaTitle = $heading;
        $metaDescription = $subheading;

        return Inertia::render('Public/Listing', [
            'items' => $items,
            'heading' => $heading,
            'subheading' => $subheading,
            'breadcrumbs' => $breadcrumbs,
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
            'filters' => [
                'sort' => $sort,
            ],
        ]);
    }
}
