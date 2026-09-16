<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Inertia\Inertia;

class CommentModerationController extends Controller
{
    public function index()
    {
        $comments = Comment::query()->with('article')->orderByDesc('id')->paginate(25);
        return Inertia::render('Admin/Comments/Index', [
            'comments' => $comments
        ]);
    }

    public function approve(Comment $comment)
    {
        $comment->status = 'approved';
        $comment->save();
        return back()->with('status','Komentar disetujui.');
    }

    public function hide(Comment $comment)
    {
        $comment->status = 'hidden';
        $comment->save();
        return back()->with('status','Komentar disembunyikan.');
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();
        return back()->with('status','Komentar dihapus (soft).');
    }
}
