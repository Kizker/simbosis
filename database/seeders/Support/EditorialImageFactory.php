<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditorialImageFactory
{
    private const PALETTES = [
        'nasional' => ['#0f766e', '#38bdf8', '#d9f99d', '#0f172a'],
        'internasional' => ['#0369a1', '#22c55e', '#bae6fd', '#0f172a'],
        'ekonomi' => ['#047857', '#0ea5e9', '#bbf7d0', '#12312b'],
        'teknologi' => ['#075985', '#14b8a6', '#a7f3d0', '#0b1120'],
        'olahraga' => ['#15803d', '#0284c7', '#ecfccb', '#102a1f'],
        'hiburan' => ['#0e7490', '#84cc16', '#cffafe', '#10212b'],
        'lifestyle' => ['#0d9488', '#60a5fa', '#dcfce7', '#14213d'],
        'kesehatan' => ['#16a34a', '#38bdf8', '#dcfce7', '#0f2f2e'],
        'otomotif' => ['#1e40af', '#22c55e', '#dbeafe', '#111827'],
        'sains' => ['#0f766e', '#2563eb', '#ccfbf1', '#0b1220'],
        'video' => ['#0369a1', '#10b981', '#e0f2fe', '#0f172a'],
        'foto' => ['#047857', '#38bdf8', '#ecfeff', '#0f172a'],
        'iklan' => ['#0f766e', '#2563eb', '#dcfce7', '#0b1220'],
    ];

    public static function make(string $kind, string $title, int $index, int $width = 1280, int $height = 800): string
    {
        $slug = Str::slug($kind . '-' . $index . '-' . Str::limit($title, 42, ''));
        $path = "editorial/{$slug}.jpg";

        if (Storage::disk('public')->exists($path)) {
            return $path;
        }

        $dir = dirname(Storage::disk('public')->path($path));
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        [$primary, $secondary, $soft, $ink] = self::palette($kind);

        self::gradient($image, $width, $height, $primary, $secondary);
        self::addAtmosphere($image, $width, $height, $soft, $ink, $index);
        self::addEditorialScene($image, $kind, $width, $height, $primary, $secondary, $soft, $ink, $index);
        self::addVignette($image, $width, $height);

        imagejpeg($image, Storage::disk('public')->path($path), 88);
        imagedestroy($image);

        return $path;
    }

    private static function palette(string $kind): array
    {
        $key = Str::of($kind)->lower()->ascii()->slug()->value();

        return self::PALETTES[$key] ?? self::PALETTES['nasional'];
    }

    private static function gradient($image, int $width, int $height, string $from, string $to): void
    {
        [$r1, $g1, $b1] = self::hex($from);
        [$r2, $g2, $b2] = self::hex($to);

        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / max(1, $height - 1);
            $r = (int) round($r1 + ($r2 - $r1) * $ratio);
            $g = (int) round($g1 + ($g2 - $g1) * $ratio);
            $b = (int) round($b1 + ($b2 - $b1) * $ratio);
            imageline($image, 0, $y, $width, $y, imagecolorallocate($image, $r, $g, $b));
        }
    }

    private static function addAtmosphere($image, int $width, int $height, string $soft, string $ink, int $index): void
    {
        [$sr, $sg, $sb] = self::hex($soft);
        [$ir, $ig, $ib] = self::hex($ink);

        for ($i = 0; $i < 22; $i++) {
            $x = (($index * 97 + $i * 173) % $width) - (int) ($width * .15);
            $y = (($index * 53 + $i * 127) % $height) - (int) ($height * .15);
            $w = (int) ($width * (.16 + (($i % 5) * .035)));
            $h = (int) ($height * (.12 + (($i % 4) * .03)));
            $color = imagecolorallocatealpha($image, $sr, $sg, $sb, 94 + ($i % 18));
            imagefilledellipse($image, $x, $y, $w, $h, $color);
        }

        for ($i = 0; $i < 9; $i++) {
            $x = ($index * 41 + $i * 167) % $width;
            $y = (int) ($height * (.5 + (($i % 4) * .07)));
            $color = imagecolorallocatealpha($image, $ir, $ig, $ib, 104);
            imagefilledrectangle($image, $x, $y, min($width, $x + 120 + ($i * 11)), $height, $color);
        }
    }

    private static function addEditorialScene($image, string $kind, int $width, int $height, string $primary, string $secondary, string $soft, string $ink, int $index): void
    {
        [$pr, $pg, $pb] = self::hex($primary);
        [$sr, $sg, $sb] = self::hex($secondary);
        [$or, $og, $ob] = self::hex($soft);
        [$ir, $ig, $ib] = self::hex($ink);

        $shadow = imagecolorallocatealpha($image, $ir, $ig, $ib, 70);
        $light = imagecolorallocatealpha($image, $or, $og, $ob, 22);
        $accent = imagecolorallocatealpha($image, $sr, $sg, $sb, 24);
        $deep = imagecolorallocatealpha($image, $pr, $pg, $pb, 32);
        $line = imagecolorallocatealpha($image, 255, 255, 255, 82);

        imagefilledrectangle($image, 0, (int) ($height * .68), $width, $height, $shadow);
        imagefilledellipse($image, (int) ($width * .76), (int) ($height * .28), (int) ($width * .36), (int) ($height * .36), $light);
        imagefilledellipse($image, (int) ($width * .21), (int) ($height * .24), (int) ($width * .28), (int) ($height * .28), $accent);

        $key = Str::of($kind)->lower()->ascii()->slug()->value();

        if (in_array($key, ['teknologi', 'sains', 'video'], true)) {
            for ($i = 0; $i < 7; $i++) {
                $x = (int) ($width * (.16 + $i * .105));
                imageline($image, $x, (int) ($height * .22), $x + 110, (int) ($height * .7), $line);
                imagefilledellipse($image, $x, (int) ($height * (.28 + ($i % 3) * .1)), 22, 22, $light);
            }
            imagefilledrectangle($image, (int) ($width * .52), (int) ($height * .28), (int) ($width * .82), (int) ($height * .58), $deep);
            imagefilledpolygon($image, [
                (int) ($width * .64), (int) ($height * .36),
                (int) ($width * .64), (int) ($height * .5),
                (int) ($width * .75), (int) ($height * .43),
            ], 3, imagecolorallocatealpha($image, 255, 255, 255, 45));
        } elseif (in_array($key, ['olahraga', 'kesehatan'], true)) {
            imagefilledellipse($image, (int) ($width * .68), (int) ($height * .42), (int) ($width * .28), (int) ($height * .36), $deep);
            for ($i = 0; $i < 6; $i++) {
                imagearc($image, (int) ($width * .68), (int) ($height * .42), 210 + $i * 24, 170 + $i * 18, 0, 360, $line);
            }
            imagefilledrectangle($image, (int) ($width * .14), (int) ($height * .48), (int) ($width * .45), (int) ($height * .56), $accent);
        } elseif (in_array($key, ['ekonomi', 'nasional', 'internasional'], true)) {
            for ($i = 0; $i < 8; $i++) {
                $x1 = (int) ($width * (.12 + $i * .095));
                $h = (int) ($height * (.18 + (($i + $index) % 5) * .045));
                imagefilledrectangle($image, $x1, (int) ($height * .66) - $h, $x1 + 58, (int) ($height * .66), $deep);
                imageline($image, $x1 + 9, (int) ($height * .66) - $h + 18, $x1 + 49, (int) ($height * .66) - $h + 18, $line);
            }
            imagefilledellipse($image, (int) ($width * .75), (int) ($height * .3), (int) ($width * .24), (int) ($height * .18), $light);
        } else {
            imagefilledrectangle($image, (int) ($width * .12), (int) ($height * .24), (int) ($width * .47), (int) ($height * .62), $deep);
            imagefilledrectangle($image, (int) ($width * .53), (int) ($height * .2), (int) ($width * .86), (int) ($height * .57), $accent);
            imagefilledellipse($image, (int) ($width * .48), (int) ($height * .42), (int) ($width * .26), (int) ($height * .24), $light);
        }

        for ($i = 0; $i < 60; $i++) {
            $x = ($index * 37 + $i * 71) % $width;
            $y = ($index * 83 + $i * 43) % $height;
            imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, 255, 255, 255, 78));
        }
    }

    private static function addVignette($image, int $width, int $height): void
    {
        $overlay = imagecolorallocatealpha($image, 4, 18, 30, 76);
        imagefilledrectangle($image, 0, 0, $width, (int) ($height * .08), $overlay);
        imagefilledrectangle($image, 0, (int) ($height * .88), $width, $height, $overlay);
    }

    private static function hex(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}
