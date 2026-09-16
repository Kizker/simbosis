<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Article $article)
    {
        abort_unless((bool) SiteSetting::get('comments_enabled', '1'), 404);
        abort_unless($article->status?->value === 'published', 404);

        $data = $request->validate([
            'name' => ['required','string','max:100'],
            'email' => ['required','email','max:190'],
            'content' => ['required','string','max:1000'],
        ]);

        Comment::query()->create([
            'article_id' => $article->id,
            'name' => trim($data['name']),
            'email' => trim($data['email']),
            'content' => trim($data['content']),
            // Public comments are shown immediately.
            'status' => 'approved',
            'ip_hash' => hash('sha256', (string)$request->ip()),
            'user_agent_hash' => hash('sha256', substr((string)$request->userAgent(), 0, 255)),
        ]);

        return redirect()
            ->to(route('article.show', $article).'#comments')
            ->with('status', 'Komentar berhasil ditambahkan.');
    }
}
