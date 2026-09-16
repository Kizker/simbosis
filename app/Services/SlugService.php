<?php

namespace App\Services;

use Illuminate\Support\Str;

class SlugService
{
    public function uniqueSlug(string $title, string $modelClass, string $column='slug', ?int $ignoreId=null): string
    {
        $base = Str::slug($title);
        $slug = $base !== '' ? $base : Str::random(8);

        $i = 1;
        while ($this->exists($modelClass, $column, $slug, $ignoreId)) {
            $i++;
            $slug = $base.'-'.$i;
        }
        return $slug;
    }

    private function exists(string $modelClass, string $column, string $slug, ?int $ignoreId): bool
    {
        $q = $modelClass::query()->where($column, $slug);
        if ($ignoreId) $q->whereKeyNot($ignoreId);
        return $q->exists();
    }
}
