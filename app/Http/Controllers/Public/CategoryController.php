<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        return redirect()->route('home');
    }

    public function show(Request $request, Category $category)
    {
        abort_unless($category->is_active, 404);

        $sort = $request->query('sort', 'latest');
        $query = $category->articles()
            ->published()
            ->with(['category','author','tags']);

        if ($sort === 'popular') {
            $query->orderByDesc('view_count');
        } else {
            $query->orderByDesc('published_at');
        }

        $items = $query->paginate(12)->withQueryString();
        $heading = $category->name;
        $subheading = $category->description;
        $breadcrumbs = [
            ['label' => 'Beranda', 'href' => route('home')],
            ['label' => 'Kategori'],
            ['label' => $category->name],
        ];
        $metaTitle = $heading;
        $metaDescription = $subheading ?: ('Kumpulan berita dalam kategori '.$category->name.'.');

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
