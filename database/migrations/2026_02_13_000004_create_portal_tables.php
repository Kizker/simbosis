<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('meta_json')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['entity_type','entity_id']);
            $table->index('action');
        });

        Schema::create('author_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->text('bio')->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt', 400)->nullable();
            $table->longText('content_html');
            $table->string('cover_image_path')->nullable();

            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('editor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();

            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable()->index();
            $table->unsignedSmallInteger('reading_time')->default(1);

            $table->unsignedBigInteger('view_count')->default(0)->index();
            $table->unsignedBigInteger('share_count')->default(0)->index();

            $table->boolean('is_breaking')->default(false)->index();
            $table->boolean('is_featured')->default(false)->index();

            $table->string('seo_title')->nullable();
            $table->string('seo_desc', 300)->nullable();
            $table->string('canonical_url')->nullable();

            $table->timestamps();

            $table->index(['status','published_at']);
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $table->fullText(['title','excerpt','content_html']);
            }

        });

        Schema::create('article_tag', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->primary(['article_id','tag_id']);
        });

        Schema::create('article_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->string('from_status');
            $table->string('to_status');
            $table->string('note', 1000)->nullable();
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['article_id','to_status']);
        });

        // Views aggregated per-day for performance.
        Schema::create('article_view_dailies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->date('viewed_date');
            $table->unsignedInteger('count')->default(0);
            $table->unique(['article_id','viewed_date']);
            $table->index(['article_id','viewed_date']);
        });

        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploader_id')->constrained('users')->cascadeOnDelete();
            $table->string('disk');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime');
            $table->unsignedBigInteger('size_bytes');
            $table->string('sha256', 64)->index();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->timestamps();
            $table->index(['disk','path']);
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('email', 190);
            $table->string('content', 1000);
            $table->string('status')->default('pending');
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent_hash', 64)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['article_id','status']);
        });

        Schema::create('ads_banners', function (Blueprint $table) {
            $table->id();
            $table->string('slot'); // header, sidebar, in_article, footer
            $table->string('title');
            $table->string('image_path')->nullable();
            $table->string('target_url');
            $table->boolean('is_active')->default(false);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['slot','is_active','starts_at','ends_at']);
        });

        Schema::create('shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->string('channel'); // wa, fb, x, telegram
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent_hash', 64)->nullable();
            $table->timestamps();

            $table->index(['article_id','channel']);
        });

        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt', 400)->nullable();
            $table->string('video_url');
            $table->string('cover_image_path')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt', 400)->nullable();
            $table->string('image_path');
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photos');
        Schema::dropIfExists('videos');
        Schema::dropIfExists('shares');
        Schema::dropIfExists('ads_banners');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('media_files');
        Schema::dropIfExists('article_view_dailies');
        Schema::dropIfExists('article_status_histories');
        Schema::dropIfExists('article_tag');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('author_profiles');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('site_settings');
    }
};
