<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('articles.update_any')
            || $user->can('articles.update_own')
            || $user->can('articles.create')
            || $user->can('articles.review');
    }

    public function view(User $user, Article $article): bool
    {
        if ($this->update($user, $article)) {
            return true;
        }

        if (! $user->can('articles.review')) {
            return false;
        }

        return in_array($article->status?->value, ['submitted', 'review', 'revision'], true);
    }

    public function create(User $user): bool
    {
        return $user->can('articles.create');
    }

    public function update(User $user, Article $article): bool
    {
        if ($user->can('articles.update_any')) return true;
        return $user->can('articles.update_own') && $article->author_id === $user->id;
    }

    public function submit(User $user, Article $article): bool
    {
        return $user->can('articles.submit') && $this->update($user, $article);
    }

    public function review(User $user): bool
    {
        return $user->can('articles.review');
    }

    public function publish(User $user): bool
    {
        return $user->can('articles.publish');
    }

    public function archive(User $user): bool
    {
        return $user->can('articles.archive');
    }
}
