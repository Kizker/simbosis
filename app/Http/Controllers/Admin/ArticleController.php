<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleUpsertRequest;
use App\Http\Requests\RevisionRequest;
use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\SiteSetting;
use App\Services\ArticleWorkflowService;
use App\Services\AuditService;
use App\Services\HtmlSanitizer;
use App\Services\MediaUploadService;
use App\Services\SlugService;
use App\Support\MediaPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Throwable;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Article::class);

        $status = $request->query('status');
        $search = $request->query('search');
        $user = Auth::user();
        $query = Article::query()->with(['category','author','editor'])->orderByDesc('updated_at');
        if ($status) $query->where('status', $status);
        if ($search) $query->where('title', 'like', "%{$search}%");

        if ($user->can('articles.update_any')) {
            // Editors and superadmins can see the full queue.
        } elseif ($user->can('articles.review') && !$user->can('articles.update_own')) {
            $query->whereIn('status', [
                ArticleStatus::Submitted,
                ArticleStatus::Review,
                ArticleStatus::Revision,
            ]);
        } else {
            $query->where('author_id', Auth::id());
        }

        $articles = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Articles/Index', [
            'articles' => $articles,
            'status' => $status,
            'search' => $search
        ]);
    }

    public function create()
    {
        $this->authorize('create', Article::class);

        return Inertia::render('Admin/Articles/Form', [
            'article' => new Article(),
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(ArticleUpsertRequest $request, SlugService $slugger, HtmlSanitizer $sanitizer, AuditService $audit, MediaUploadService $uploader)
    {
        $this->authorize('create', Article::class);

        $data = $request->validated();
        $content = $sanitizer->sanitize($data['content_html']);
        $readingTime = $this->calcReadingTime($content);

        $coverImagePath = MediaPath::normalizeStoragePath($data['cover_image_path'] ?? null);
        if ($request->hasFile('cover_image_file')) {
            $coverImagePath = $this->storeImageWithFallback($request->file('cover_image_file'), $uploader);
        }

        $article = Article::query()->create([
            'title' => $data['title'],
            'slug' => $slugger->uniqueSlug($data['title'], Article::class),
            'excerpt' => $data['excerpt'] ?? null,
            'content_html' => $content,
            'cover_image_path' => $coverImagePath,
            'author_id' => Auth::id(),
            'editor_id' => null,
            'category_id' => $data['category_id'],
            'status' => 'draft',
            'published_at' => null,
            'reading_time' => $readingTime,
            'view_count' => 0,
            'share_count' => 0,
            'is_breaking' => (bool)($data['is_breaking'] ?? false),
            'is_featured' => (bool)($data['is_featured'] ?? false),
            'seo_title' => $data['seo_title'] ?? null,
            'seo_desc' => $data['seo_desc'] ?? null,
            'canonical_url' => $data['canonical_url'] ?? null,
        ]);

        $tagIds = $data['tag_ids'] ?? [];
        if (!empty($data['new_tags'])) {
            foreach ($data['new_tags'] as $newTagName) {
                $tag = Tag::firstOrCreate(
                    ['slug' => $slugger->uniqueSlug($newTagName, Tag::class)],
                    ['name' => $newTagName]
                );
                $tagIds[] = $tag->id;
            }
        }
        $article->tags()->sync($tagIds);

        $audit->log('article.created', Article::class, $article->id, ['status' => 'draft']);
        Cache::forget('public:home');
        Cache::forget('public:sitemap');

        return redirect()->route('admin.articles.edit', $article)->with('status', 'Artikel berhasil dibuat.');
    }

    public function edit(Article $article)
    {
        $this->authorize('view', $article);

        return Inertia::render('Admin/Articles/Form', [
            'article' => $article->load(['tags', 'statusHistory' => function($q) {
                $q->latest()->take(5);
            }]),
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(
        ArticleUpsertRequest $request,
        Article $article,
        SlugService $slugger,
        HtmlSanitizer $sanitizer,
        AuditService $audit,
        MediaUploadService $uploader,
        ArticleWorkflowService $wf
    )
    {
        $this->authorize('update', $article);

        $data = $request->validated();
        $content = $sanitizer->sanitize($data['content_html']);
        $readingTime = $this->calcReadingTime($content);

        $slugOld = $article->slug;

        $coverImagePath = MediaPath::normalizeStoragePath($data['cover_image_path'] ?? null);
        if ($request->hasFile('cover_image_file')) {
            if ($article->cover_image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($article->cover_image_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($article->cover_image_path);
            }
            $coverImagePath = $this->storeImageWithFallback($request->file('cover_image_file'), $uploader);
        }

        $article->fill([
            'title' => $data['title'],
            'excerpt' => $data['excerpt'] ?? null,
            'content_html' => $content,
            'cover_image_path' => $coverImagePath,
            'category_id' => $data['category_id'],
            'reading_time' => $readingTime,
            'is_breaking' => (bool)($data['is_breaking'] ?? false),
            'is_featured' => (bool)($data['is_featured'] ?? false),
            'seo_title' => $data['seo_title'] ?? null,
            'seo_desc' => $data['seo_desc'] ?? null,
            'canonical_url' => $data['canonical_url'] ?? null,
        ]);

        if ($request->boolean('regenerate_slug')) {
            $article->slug = $slugger->uniqueSlug($data['title'], Article::class, 'slug', $article->id);
        }

        $article->save();
        $tagIds = $data['tag_ids'] ?? [];
        if (!empty($data['new_tags'])) {
            foreach ($data['new_tags'] as $newTagName) {
                $tag = Tag::firstOrCreate(
                    ['slug' => $slugger->uniqueSlug($newTagName, Tag::class)],
                    ['name' => $newTagName]
                );
                $tagIds[] = $tag->id;
            }
        }
        $article->tags()->sync($tagIds);

        if ($slugOld !== $article->slug) {
            $redirects = json_decode((string) SiteSetting::get('slug_redirects', '{}'), true) ?: [];
            $redirects["/artikel/{$slugOld}"] = "/artikel/{$article->slug}";
            SiteSetting::set('slug_redirects', json_encode($redirects));
        }

        $audit->log('article.updated', Article::class, $article->id, ['status' => $article->status?->value]);
        Cache::forget('public:home');
        Cache::forget("public:related:{$article->id}");
        Cache::forget('public:sitemap');

        if ($request->boolean('publish_now')) {
            $this->authorize('publish', Article::class);

            if ($article->status?->value !== 'published') {
                $wf->publish($article, Auth::id());
                $audit->log('article.published', Article::class, $article->id);
            } else {
                // Already published: keep status, but treat this action as re-publish of updated content.
                $audit->log('article.republished', Article::class, $article->id);
            }

            Cache::forget('public:trending');
            Cache::forget('public:home');
            Cache::forget("public:related:{$article->id}");
            Cache::forget('public:sitemap');

            return back()->with('status', 'Perubahan berhasil disimpan dan artikel dipublish.');
        }

        return back()->with('status', 'Artikel berhasil diperbarui.');
    }

    public function submit(Article $article, ArticleWorkflowService $wf, AuditService $audit)
    {
        $this->authorize('submit', $article);
        $wf->submit($article, Auth::id());
        $audit->log('article.submitted', Article::class, $article->id);
        return back()->with('status', 'Artikel berhasil diajukan.');
    }

    public function startReview(Article $article, ArticleWorkflowService $wf, AuditService $audit)
    {
        $this->authorize('review', Article::class);
        $wf->startReview($article, Auth::id());
        $audit->log('article.review.started', Article::class, $article->id);
        return back()->with('status', 'Artikel berhasil masuk tahap review.');
    }

    public function requestRevision(RevisionRequest $request, Article $article, ArticleWorkflowService $wf, AuditService $audit)
    {
        $this->authorize('review', Article::class);
        $wf->requestRevision($article, Auth::id(), $request->validated()['note']);
        $audit->log('article.revision.requested', Article::class, $article->id);
        return back()->with('status', 'Permintaan revisi berhasil dikirim.');
    }

    public function publish(Article $article, ArticleWorkflowService $wf, AuditService $audit)
    {
        $this->authorize('publish', Article::class);
        $wf->publish($article, Auth::id());
        $audit->log('article.published', Article::class, $article->id);
        Cache::forget('public:home');
        Cache::forget('public:trending');
        Cache::forget('public:sitemap');
        return back()->with('status', 'Artikel berhasil dipublish.');
    }

    public function archive(Article $article, ArticleWorkflowService $wf, AuditService $audit)
    {
        $this->authorize('archive', Article::class);
        $wf->archive($article, Auth::id());
        $audit->log('article.archived', Article::class, $article->id);
        Cache::forget('public:home');
        Cache::forget('public:trending');
        Cache::forget('public:sitemap');
        return back()->with('status', 'Artikel berhasil diarsipkan.');
    }

    private function calcReadingTime(string $html): int
    {
        $text = trim(strip_tags($html));
        preg_match_all('/\p{L}+/u', $text, $m);
        $words = count($m[0]);
        $minutes = (int) ceil(max(1, $words) / 200);
        return min(60, max(1, $minutes));
    }

    private function storeImageWithFallback(UploadedFile $file, MediaUploadService $uploader): string
    {
        try {
            return $uploader->uploadPublicImage($file, Auth::id(), '')->path;
        } catch (Throwable) {
            return $file->store('', 'public');
        }
    }
}
