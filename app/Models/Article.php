<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Article extends Model
{
    protected $fillable = [
        'title','slug','excerpt','content_html','cover_image_path',
        'author_id','editor_id','category_id',
        'status','published_at','reading_time',
        'view_count','share_count','is_breaking','is_featured',
        'seo_title','seo_desc','canonical_url'
    ];

    protected function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
            'published_at' => 'datetime',
            'is_breaking' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function author(): BelongsTo { return $this->belongsTo(User::class, 'author_id'); }
    public function editor(): BelongsTo { return $this->belongsTo(User::class, 'editor_id'); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function tags(): BelongsToMany { return $this->belongsToMany(Tag::class, 'article_tag'); }
    public function statusHistory(): HasMany { return $this->hasMany(ArticleStatusHistory::class); }
    public function viewsDaily(): HasMany { return $this->hasMany(ArticleViewDaily::class); }
    public function shares(): HasMany { return $this->hasMany(Share::class); }
    public function comments(): HasMany { return $this->hasMany(Comment::class); }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', ArticleStatus::Published)->whereNotNull('published_at');
    }
    public function scopeBreaking(Builder $q): Builder { return $q->published()->where('is_breaking', true); }
    public function scopeFeatured(Builder $q): Builder { return $q->published()->where('is_featured', true); }
}
