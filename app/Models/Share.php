<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Share extends Model
{
    protected $fillable = ['article_id','channel','ip_hash','user_agent_hash'];
    public function article(): BelongsTo { return $this->belongsTo(Article::class); }
}
