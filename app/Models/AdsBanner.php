<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class AdsBanner extends Model
{
    protected $fillable = ['slot','title','image_path','target_url','is_active','starts_at','ends_at'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function scopeActiveForSlot(Builder $q, string $slot): Builder
    {
        $now = Carbon::now();
        return $q->where('slot', $slot)
            ->where('is_active', true)
            ->where(function($w) use ($now) {
                $w->whereNull('starts_at')->orWhere('starts_at','<=',$now);
            })
            ->where(function($w) use ($now) {
                $w->whereNull('ends_at')->orWhere('ends_at','>=',$now);
            })
            ->orderByDesc('updated_at');
    }
}
