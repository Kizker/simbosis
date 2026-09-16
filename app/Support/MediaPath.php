<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class MediaPath
{
    public static function normalizeStoragePath(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $path = trim($path);
        if ($path === '') {
            return null;
        }

        $path = preg_replace('#^https?://localhost/storage/#i', '', $path) ?? $path;
        $path = preg_replace('#^/storage/#i', '', $path) ?? $path;
        $path = preg_replace('#^storage/#i', '', $path) ?? $path;

        return ltrim($path, '/');
    }

    public static function publicUrl(?string $path): ?string
    {
        $original = $path;
        $normalized = self::normalizeStoragePath($path);
        if ($normalized === null) {
            return null;
        }

        if (preg_match('#^https?://#i', $original ?? '') === 1 && preg_match('#^https?://localhost/storage/#i', $original ?? '') !== 1) {
            return $original;
        }

        return Storage::url($normalized);
    }

    public static function publicUrlOrDefault(?string $path, string $fallback = 'placeholders/photo-1.svg'): string
    {
        return self::publicUrl($path) ?? Storage::url($fallback);
    }
}
