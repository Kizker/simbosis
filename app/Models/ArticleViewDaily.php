<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleViewDaily extends Model
{
    public $timestamps = false;
    protected $fillable = ['article_id','viewed_date','count'];
    protected function casts(): array { return ['viewed_date' => 'date']; }
    public function article(): BelongsTo { return $this->belongsTo(Article::class); }
}
