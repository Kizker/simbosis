<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\ArticleController as PublicArticleController;
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\TagController;
use App\Http\Controllers\Public\AuthorController;
use App\Http\Controllers\Public\TrendingController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\Public\CommentController as PublicCommentController;
use App\Http\Controllers\Public\ShareController;
use App\Http\Controllers\Public\SitemapController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\CommentModerationController;
use App\Http\Controllers\Admin\AdsBannerController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ContactMessageController;

Route::middleware(['web'])->group(function () {

    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/semua-berita', [TrendingController::class, 'index'])->name('trending');

    Route::get('/kategori', [CategoryController::class, 'index'])->name('category.index');
    Route::get('/kategori/{category:slug}', [CategoryController::class, 'show'])->name('category.show');
    Route::get('/tag/{tag:slug}', [TagController::class, 'show'])->name('tag.show');
    Route::get('/penulis/{user}', [AuthorController::class, 'show'])->name('author.show');

    Route::get('/tentang-kami', [PageController::class, 'about'])->name('page.about');
    Route::get('/kontak', [PageController::class, 'contact'])->name('page.contact');
    Route::post('/kontak', [PageController::class, 'sendContact'])->middleware(['throttle:contact'])->name('page.contact.send');

    Route::get('/cari/live', [SearchController::class, 'live'])->name('search.live');
    Route::get('/cari', [SearchController::class, 'index'])->name('search');
    Route::get('/search', [SearchController::class, 'index']);

    Route::get('/artikel/{article:slug}', [PublicArticleController::class, 'show'])->name('article.show');
    Route::get('/articles/{article:slug}', [PublicArticleController::class, 'show']);
    Route::post('/artikel/{article}/komentar', [PublicCommentController::class, 'store'])
        ->middleware(['throttle:comment'])
        ->name('article.comment.store');
    Route::post('/articles/{article:slug}/comments', [PublicCommentController::class, 'store'])
        ->middleware(['throttle:comment']);

    Route::post('/share/{article}', [ShareController::class, 'store'])
        ->middleware(['throttle:share'])
        ->name('share.store');
    Route::get('/share/{article:slug}/{channel}', [ShareController::class, 'redirect'])
        ->middleware(['throttle:share'])
        ->name('share.redirect');

    Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.xml');
    Route::get('/pwa/offline', fn() => response()->view('public.offline')->header('Cache-Control','no-store'))->name('pwa.offline');

    // Admin / CMS
    Route::prefix('harmony-access')->name('admin.')->middleware(['auth'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('articles', ArticleController::class)->except(['show','destroy']);
        Route::get('articles/{article}/preview', [PublicArticleController::class, 'preview'])->name('articles.preview');
        Route::post('articles/{article}/submit', [ArticleController::class, 'submit'])->middleware('permission:articles.submit')->name('articles.submit');
        Route::post('articles/{article}/start-review', [ArticleController::class, 'startReview'])->middleware('permission:articles.review')->name('articles.startReview');
        Route::post('articles/{article}/request-revision', [ArticleController::class, 'requestRevision'])->middleware('permission:articles.review')->name('articles.requestRevision');
        Route::post('articles/{article}/publish', [ArticleController::class, 'publish'])->middleware(['permission:articles.publish','throttle:publish'])->name('articles.publish');
        Route::post('articles/{article}/archive', [ArticleController::class, 'archive'])->middleware('permission:articles.archive')->name('articles.archive');

        Route::resource('categories', AdminCategoryController::class)->middleware('permission:categories.manage');
        Route::get('tags/search', [AdminTagController::class, 'search'])->name('tags.search');
        Route::resource('tags', AdminTagController::class)->middleware('permission:tags.manage');

        Route::get('media', [MediaController::class, 'index'])->middleware('permission:media.manage')->name('media.index');
        Route::post('media', [MediaController::class, 'store'])->middleware(['permission:media.upload','throttle:upload'])->name('media.store');
        Route::delete('media/{media}', [MediaController::class, 'destroy'])->middleware('permission:media.manage')->name('media.destroy');

        Route::get('comments', [CommentModerationController::class, 'index'])->middleware('permission:comments.moderate')->name('comments.index');
        Route::post('comments/{comment}/approve', [CommentModerationController::class, 'approve'])->middleware('permission:comments.moderate')->name('comments.approve');
        Route::post('comments/{comment}/hide', [CommentModerationController::class, 'hide'])->middleware('permission:comments.moderate')->name('comments.hide');
        Route::delete('comments/{comment}', [CommentModerationController::class, 'destroy'])->middleware('permission:comments.moderate')->name('comments.destroy');

        Route::resource('ads', AdsBannerController::class)->middleware('permission:ads.manage');

        Route::get('settings', [SettingsController::class, 'index'])->middleware('permission:site_settings.manage')->name('settings.index');
        Route::post('settings', [SettingsController::class, 'update'])->middleware('permission:site_settings.manage')->name('settings.update');

        Route::get('messages', [ContactMessageController::class, 'index'])->middleware('permission:messages.manage')->name('messages.index');
        Route::post('messages/{message}/reply', [ContactMessageController::class, 'reply'])->middleware('permission:messages.manage')->name('messages.reply');
        Route::post('messages/{message}/mark-as-read', [ContactMessageController::class, 'markAsRead'])->middleware('permission:messages.manage')->name('messages.markAsRead');
        Route::delete('messages/{message}', [ContactMessageController::class, 'destroy'])->middleware('permission:messages.manage')->name('messages.destroy');


        Route::get('seo', [SettingsController::class, 'seo'])->middleware('permission:seo.manage')->name('settings.seo');
        Route::post('seo', [SettingsController::class, 'updateSeo'])->middleware('permission:seo.manage')->name('settings.seo.update');

        Route::get('sitemap', [SettingsController::class, 'sitemap'])->middleware('permission:sitemap.manage')->name('settings.sitemap');
        Route::post('sitemap', [SettingsController::class, 'updateSitemap'])->middleware('permission:sitemap.manage')->name('settings.sitemap.update');

        Route::get('pages', [SettingsController::class, 'pages'])->middleware('permission:site_settings.manage')->name('settings.pages');
        Route::post('pages', [SettingsController::class, 'updatePages'])->middleware('permission:site_settings.manage')->name('settings.pages.update');

        Route::get('about', [SettingsController::class, 'aboutEdit'])->middleware('permission:site_settings.manage')->name('settings.about');
        Route::post('about', [SettingsController::class, 'updateAbout'])->middleware('permission:site_settings.manage')->name('settings.about.update');

        Route::get('social', [SettingsController::class, 'social'])->middleware('permission:site_settings.manage')->name('settings.social');
        Route::post('social', [SettingsController::class, 'updateSocial'])->middleware('permission:site_settings.manage')->name('settings.social.update');
        Route::get('users', [UserRoleController::class, 'index'])->middleware('permission:users.manage')->name('users.index');
        Route::get('users/create', [UserRoleController::class, 'create'])->middleware('permission:users.manage')->name('users.create');
        Route::post('users', [UserRoleController::class, 'store'])->middleware('permission:users.manage')->name('users.store');
        Route::get('users/{user}/edit', [UserRoleController::class, 'edit'])->middleware('permission:users.manage')->name('users.edit');
        Route::put('users/{user}', [UserRoleController::class, 'update'])->middleware('permission:users.manage')->name('users.update');
        Route::delete('users/{user}', [UserRoleController::class, 'destroy'])->middleware('permission:users.manage')->name('users.destroy');
        Route::post('users/{user}/roles', [UserRoleController::class, 'syncRoles'])->middleware('permission:roles.manage')->name('users.roles.sync');
        Route::post('users/{user}/permissions', [UserRoleController::class, 'syncPermissions'])->middleware('permission:permissions.manage')->name('users.permissions.sync');

        Route::get('audit-logs', [AuditLogController::class, 'index'])->middleware('permission:audit_logs.view')->name('audit.index');
    });


    Route::prefix('harmony-access')->group(function () {
        require __DIR__.'/auth.php';
    });
});

