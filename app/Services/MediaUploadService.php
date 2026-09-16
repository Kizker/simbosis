<?php

namespace App\Services;

use App\Models\MediaFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class MediaUploadService
{
    public function uploadPublicImage(UploadedFile $file, int $uploaderId, string $folder = 'media'): MediaFile
    {
        $this->validateUpload($file);

        $bytes = file_get_contents($file->getRealPath());
        $sha256 = hash('sha256', $bytes);

        $ext = $file->getClientOriginalExtension() ?: 'png';
        
        // Generate random name with 10 chars
        $fileName = Str::random(10) . '.' . $ext;
        
        // Store directly in root without any folder prefix
        $path = $file->storeAs('', $fileName, 'public');

        try {
            $img = Image::read($file->getRealPath());
            $width = method_exists($img, 'width') ? $img->width() : null;
            $height = method_exists($img, 'height') ? $img->height() : null;
        } catch (\Throwable) {
            $width = null;
            $height = null;
        }

        return MediaFile::query()->create([
            'uploader_id'=>$uploaderId,
            'disk'=>'public',
            'path'=>$path,
            'original_name'=>$file->getClientOriginalName(),
            'mime'=>$file->getClientMimeType(),
            'size_bytes'=>$file->getSize(),
            'sha256'=>$sha256,
            'width'=>$width,
            'height'=>$height,
        ]);
    }

    private function validateUpload(UploadedFile $file): void
    {
        $max = (int) config('security.uploads.max_bytes', 5*1024*1024);
        $allowedMimes = config('security.uploads.allowed_mimes', []);
        $allowedExt = config('security.uploads.allowed_ext', []);

        $mime = $file->getClientMimeType();
        $ext = strtolower($file->getClientOriginalExtension() ?: '');

        if ($file->getSize() > $max) abort(422, 'File terlalu besar.');
        if (!in_array($mime, $allowedMimes, true)) abort(422, 'MIME tidak diizinkan.');
        if ($ext !== '' && !in_array($ext, $allowedExt, true)) abort(422, 'Ekstensi tidak diizinkan.');
    }
}
