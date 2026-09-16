<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaFile extends Model
{
    protected $fillable = ['uploader_id','disk','path','original_name','mime','size_bytes','sha256','width','height'];
    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploader_id'); }
}
