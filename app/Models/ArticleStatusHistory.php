<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleStatusHistory extends Model
{
    protected $fillable = ['article_id','from_status','to_status','note','changed_by'];

    public function article(): BelongsTo { return $this->belongsTo(Article::class); }
    public function actor(): BelongsTo { return $this->belongsTo(User::class, 'changed_by'); }
}
