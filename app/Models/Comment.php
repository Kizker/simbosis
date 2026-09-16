<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = ['article_id','name','email','content','status','ip_hash','user_agent_hash'];
    public function article(): BelongsTo { return $this->belongsTo(Article::class); }
}
