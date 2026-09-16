<?php

namespace App\Services;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ArticleWorkflowService
{
    public function submit(Article $article, int|User $actor): Article
    {
        return $this->transition($article, $this->resolveActorId($actor), ArticleStatus::Submitted, null);
    }

    public function startReview(Article $article, int|User $actor): Article
    {
        return $this->transition($article, $this->resolveActorId($actor), ArticleStatus::Review, null);
    }

    public function moveToReview(Article $article, int|User $actor): Article
    {
        return $this->startReview($article, $actor);
    }

    public function requestRevision(Article $article, int|User $actor, string $note): Article
    {
        if (trim($note) === '') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'note' => 'Catatan revisi wajib diisi agar penulis mengetahui apa yang perlu diperbaiki.',
            ]);
        }

        return $this->transition($article, $this->resolveActorId($actor), ArticleStatus::Revision, $note);
    }

    public function publish(Article $article, int|User $actor, mixed $publishedAt = null): Article
    {
        if (trim((string) $article->seo_title) === '' || trim((string) $article->seo_desc) === '') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'seo_title' => 'Judul SEO wajib diisi sebelum artikel bisa diterbitkan.',
                'seo_desc' => 'Deskripsi SEO wajib diisi sebelum artikel bisa diterbitkan.',
            ]);
        }

        $actorId = $this->resolveActorId($actor);

        return DB::transaction(function () use ($article, $actorId, $publishedAt) {
            $from = $article->status?->value ?? ArticleStatus::Draft->value;

            $article->status = ArticleStatus::Published;
            $article->published_at = $publishedAt ?? $article->published_at ?? now();
            $article->save();

            ArticleStatusHistory::query()->create([
                'article_id' => $article->id,
                'from_status' => $from,
                'to_status' => ArticleStatus::Published->value,
                'note' => null,
                'changed_by' => $actorId,
            ]);

            app(AuditService::class)->log('article.publish', Article::class, $article->id, [
                'from_status' => $from,
                'to_status' => ArticleStatus::Published->value,
            ], $actorId);

            return $article->refresh();
        });
    }

    public function archive(Article $article, int|User $actor): Article
    {
        return $this->transition($article, $this->resolveActorId($actor), ArticleStatus::Archived, null);
    }

    private function transition(Article $article, int $actorId, ArticleStatus $to, ?string $note): Article
    {
        return DB::transaction(function () use ($article, $actorId, $to, $note) {
            $from = $article->status?->value ?? ArticleStatus::Draft->value;

            $article->status = $to;
            $article->save();

            ArticleStatusHistory::query()->create([
                'article_id' => $article->id,
                'from_status' => $from,
                'to_status' => $to->value,
                'note' => $note,
                'changed_by' => $actorId,
            ]);

            app(AuditService::class)->log('article.'.$to->value, Article::class, $article->id, [
                'from_status' => $from,
                'to_status' => $to->value,
                'note' => $note,
            ], $actorId);

            return $article->refresh();
        });
    }

    private function resolveActorId(int|User $actor): int
    {
        return $actor instanceof User ? (int) $actor->getKey() : $actor;
    }
}
