<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleViewDaily;
use App\Models\SiteSetting;
use App\Services\ArticleContentRenderer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ArticleController extends Controller
{
    public function show(Request $request, Article $article)
    {
        abort_unless($article->status?->value === 'published', 404);

        return $this->renderArticlePage($request, $article);
    }

    public function preview(Request $request, Article $article)
    {
        $this->authorize('view', $article);

        return $this->renderArticlePage($request, $article, true);
    }

    private function renderArticlePage(Request $request, Article $article, bool $previewMode = false)
    {
        $article->load(['author.authorProfile', 'category', 'tags']);

        if (! $previewMode) {
            $sessionKey = "viewed_article_{$article->id}";
            if (! $request->session()->has($sessionKey)) {
                $request->session()->put($sessionKey, true);

                DB::transaction(function () use ($article) {
                    $article->increment('view_count');

                    ArticleViewDaily::query()->updateOrCreate(
                        ['article_id' => $article->id, 'viewed_date' => now()->toDateString()],
                        ['count' => DB::raw('count + 1')]
                    );
                });
            }
        }

        $related = Cache::remember("public:related:{$article->id}", now()->addMinutes(15), function () use ($article) {
            $tagIds = $article->tags->pluck('id')->all();

            return Article::query()
                ->published()
                ->whereKeyNot($article->id)
                ->where(function ($q) use ($article, $tagIds) {
                    $q->where('category_id', $article->category_id);
                    if (!empty($tagIds)) {
                        $q->orWhereHas('tags', fn($t) => $t->whereIn('tags.id', $tagIds));
                    }
                })
                ->with(['category','author'])
                ->orderByDesc('published_at')
                ->take(6)->get();
        });

        $metaTitle = $article->seo_title ?: $article->title;
        $metaDescription = $article->seo_desc ?: ($article->excerpt ?: $article->title);
        $canonicalUrl = $article->canonical_url ?: ($previewMode ? route('admin.articles.preview', $article) : route('article.show', $article));
        $ogImage = $article->cover_image_path ? Storage::url($article->cover_image_path) : null;
        $breadcrumbs = [
            ['label' => 'Beranda', 'href' => route('home')],
            ['label' => $article->category->name, 'href' => route('category.show', $article->category)],
            ['label' => $article->title],
        ];
        $shareLinks = [
            'wa' => 'https://wa.me/?text='.urlencode($article->title.' '.$canonicalUrl),
            'fb' => 'https://www.facebook.com/sharer/sharer.php?u='.urlencode($canonicalUrl),
            'x' => 'https://twitter.com/intent/tweet?text='.urlencode($article->title).'&url='.urlencode($canonicalUrl),
            'telegram' => 'https://t.me/share/url?url='.urlencode($canonicalUrl).'&text='.urlencode($article->title),
        ];
        $commentsEnabled = ! $previewMode && (bool) SiteSetting::get('comments_enabled', '1');
        $comments = $commentsEnabled
            ? $article->comments()->where('status', 'approved')->latest()->take(50)->get()
            : collect();
        $renderedContentHtml = app(ArticleContentRenderer::class)->render($article->content_html);
        $schemaArticle = [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $article->title,
            'description' => $metaDescription,
            'datePublished' => optional($article->published_at)?->toIso8601String(),
            'dateModified' => optional($article->updated_at)?->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $article->author->name,
            ],
            'mainEntityOfPage' => $canonicalUrl,
            'image' => $ogImage ? [$ogImage] : [],
        ];
        $schemaBreadcrumb = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Beranda',
                    'item' => route('home'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $article->category->name,
                    'item' => route('category.show', $article->category),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $article->title,
                    'item' => $canonicalUrl,
                ],
            ],
        ];

        return Inertia::render('Public/ArticleShow', compact(
            'article',
            'related',
            'metaTitle',
            'metaDescription',
            'canonicalUrl',
            'ogImage',
            'breadcrumbs',
            'shareLinks',
            'previewMode',
            'commentsEnabled',
            'comments',
            'renderedContentHtml',
            'schemaArticle',
            'schemaBreadcrumb'
        ));
    }
}
