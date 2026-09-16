<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaFile;
use App\Services\MediaUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        // Auto-sync files from storage/app/public to media_files table
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $files = $disk->allFiles();
        
        $existingPaths = MediaFile::pluck('path')->toArray();
        $existingPathsSet = array_flip($existingPaths);
        $allFilesSet = array_flip($files);

        // Remove missing files from DB
        $missingInStorage = array_diff($existingPaths, $files);
        if (!empty($missingInStorage)) {
            MediaFile::whereIn('path', $missingInStorage)->delete();
        }

        $imageExtensions = ['jpg','jpeg','png','gif','webp'];
        $userId = Auth::id() ?? 1;

        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $imageExtensions)) continue;

            if (!isset($existingPathsSet[$file])) {
                $absPath = $disk->path($file);
                if (file_exists($absPath)) {
                    MediaFile::create([
                        'uploader_id' => $userId,
                        'disk' => 'public',
                        'path' => $file,
                        'original_name' => basename($file),
                        'mime' => mime_content_type($absPath) ?: 'image/'.$ext,
                        'size_bytes' => filesize($absPath),
                        'sha256' => hash_file('sha256', $absPath),
                        'width' => null,
                        'height' => null,
                    ]);
                }
            }
        }

        $query = MediaFile::query()->with('uploader');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('original_name', 'like', "%{$search}%")
                  ->orWhere('path', 'like', "%{$search}%");
        }
        
        $media = $query->orderByDesc('id')->paginate(24)->withQueryString();
        
        return Inertia::render('Admin/Media/Index', [
            'media' => $media,
            'search' => $request->search,
        ]);
    }

    public function store(Request $request, MediaUploadService $uploader)
    {
        $request->validate(['file' => ['required','file']]);

        $media = $uploader->uploadPublicImage($request->file('file'), Auth::id());
        $url = asset('storage/'.$media->path);

        return response()->json([
            'id' => $media->id,
            'location' => $url,
            'url' => $url,
            'path' => $media->path,
            'mime' => $media->mime,
        ]);
    }

    public function destroy(MediaFile $media)
    {
        $path = $media->path;
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
        }
        $media->delete();
        
        return back()->with('status', 'File media berhasil dihapus.');
    }
}
