<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthorController extends Controller
{
    public function show(Request $request, User $user)
    {
        $user->load('authorProfile');
        $sort = $request->query('sort', 'latest');

        $query = $user->articles()
            ->published()
            ->with(['category','tags']);

        if ($sort === 'popular') {
            $query->orderByDesc('view_count');
        } else {
            $query->orderByDesc('published_at');
        }

        $author = $user;
        $profile = $user->authorProfile;
        $items = $query->paginate(12)->withQueryString();
        $metaTitle = 'Penulis: '.$author->name;
        $metaDescription = $profile?->bio ?: ('Artikel oleh '.$author->name.'.');

        return Inertia::render('Public/AuthorShow', compact('author', 'profile', 'items', 'metaTitle', 'metaDescription'));
    }
}
