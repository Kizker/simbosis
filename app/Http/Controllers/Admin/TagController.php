<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Services\SlugService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $tags = Tag::query()
            ->when($search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Admin/Tags/Index', [
            'tags' => $tags,
            'search' => $search ?? ''
        ]);
    }

    public function search(Request $request)
    {
        $search = $request->input('q');
        $tags = Tag::query()
            ->when($search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($tags);
    }

    public function create()
    {
        return Inertia::render('Admin/Tags/Form', [
            'tag' => new Tag()
        ]);
    }

    public function store(Request $request, SlugService $slugger)
    {
        $data = $request->validate(['name' => ['required','string','max:80']]);
        $tag = Tag::query()->create([
            'name' => $data['name'],
            'slug' => $slugger->uniqueSlug($data['name'], Tag::class),
        ]);

        return redirect()->route('admin.tags.edit', $tag)->with('status','Tag dibuat.');
    }

    public function edit(Tag $tag)
    {
        return Inertia::render('Admin/Tags/Form', [
            'tag' => $tag
        ]);
    }

    public function update(Request $request, Tag $tag, SlugService $slugger)
    {
        $data = $request->validate([
            'name' => ['required','string','max:80'],
            'regenerate_slug' => ['sometimes','boolean'],
        ]);

        $tag->name = $data['name'];
        if ($request->boolean('regenerate_slug')) {
            $tag->slug = $slugger->uniqueSlug($data['name'], Tag::class, 'slug', $tag->id);
        }
        $tag->save();

        return back()->with('status','Tag diperbarui.');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();
        return back()->with('status','Tag dihapus.');
    }
}
