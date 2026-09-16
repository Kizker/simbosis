<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\SlugService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categories = Category::query()
            ->when($search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'search' => $search ?? ''
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Categories/Form', [
            'category' => new Category()
        ]);
    }

    public function store(Request $request, SlugService $slugger)
    {
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'description' => ['nullable','string','max:500'],
            'is_active' => ['sometimes','boolean'],
        ]);

        $category = Category::query()->create([
            'name' => $data['name'],
            'slug' => $slugger->uniqueSlug($data['name'], Category::class),
            'description' => $data['description'] ?? null,
            'is_active' => (bool)($data['is_active'] ?? true),
        ]);

        return redirect()->route('admin.categories.edit', $category)->with('status','Kategori dibuat.');
    }

    public function edit(Category $category)
    {
        return Inertia::render('Admin/Categories/Form', [
            'category' => $category
        ]);
    }

    public function update(Request $request, Category $category, SlugService $slugger)
    {
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'description' => ['nullable','string','max:500'],
            'is_active' => ['sometimes','boolean'],
            'regenerate_slug' => ['sometimes','boolean'],
        ]);

        $category->name = $data['name'];
        $category->description = $data['description'] ?? null;
        $category->is_active = (bool)($data['is_active'] ?? false);
        if ($request->boolean('regenerate_slug')) {
            $category->slug = $slugger->uniqueSlug($data['name'], Category::class, 'slug', $category->id);
        }
        $category->save();

        return back()->with('status','Kategori diperbarui.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('status','Kategori dihapus.');
    }
}
