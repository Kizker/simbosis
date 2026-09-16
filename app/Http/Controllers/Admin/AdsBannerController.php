<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdsBanner;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdsBannerController extends Controller
{
    public function index(Request $request)
    {
        $query = AdsBanner::query()->orderByDesc('id');
        
        if ($request->has('q') && $request->q !== null) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }

        $ads = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Ads/Index', [
            'ads' => $ads,
            'filters' => ['q' => $request->q]
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Ads/Form', [
            'ad' => new AdsBanner()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'slot' => ['required','in:header,sidebar,in_article,footer'],
            'title' => ['required','string','max:120'],
            'image' => ['nullable','image','max:2048'],
            'target_url' => ['required','url','max:500'],
            'is_active' => ['sometimes','boolean'],
            'starts_at' => ['nullable','date'],
            'ends_at' => ['nullable','date','after_or_equal:starts_at'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('ads', 'public');
        }

        $ad = AdsBanner::query()->create([
            'slot'=>$data['slot'],
            'title'=>$data['title'],
            'image_path'=>$imagePath,
            'target_url'=>$data['target_url'],
            'is_active'=>(bool)($data['is_active'] ?? false),
            'starts_at'=>$data['starts_at'] ?? null,
            'ends_at'=>$data['ends_at'] ?? null,
        ]);

        return redirect()->route('admin.ads.edit', $ad)->with('status','Iklan dibuat.');
    }

    public function edit(AdsBanner $ad)
    {
        return Inertia::render('Admin/Ads/Form', [
            'ad' => $ad
        ]);
    }

    public function update(Request $request, AdsBanner $ad)
    {
        $data = $request->validate([
            'slot' => ['required','in:header,sidebar,in_article,footer'],
            'title' => ['required','string','max:120'],
            'image' => ['nullable','image','max:2048'],
            'target_url' => ['required','url','max:500'],
            'is_active' => ['sometimes','boolean'],
            'starts_at' => ['nullable','date'],
            'ends_at' => ['nullable','date','after_or_equal:starts_at'],
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('ads', 'public');
        } else {
            $data['image_path'] = $ad->image_path;
        }

        $ad->fill($data);
        $ad->is_active = (bool)($data['is_active'] ?? false);
        $ad->save();

        return back()->with('status','Iklan diperbarui.');
    }

    public function destroy(AdsBanner $ad)
    {
        $ad->delete();
        return back()->with('status','Iklan dihapus.');
    }
}
