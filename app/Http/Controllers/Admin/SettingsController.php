<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Settings/Index', [
            'comments_enabled' => SiteSetting::get('comments_enabled','1'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'comments_enabled' => ['sometimes','boolean'],
        ]);

        SiteSetting::set('comments_enabled', $request->boolean('comments_enabled') ? '1':'0');

        return back()->with('status','Pengaturan disimpan.');
    }

    public function seo()
    {
        return Inertia::render('Admin/Settings/Seo', [
            'site_name' => SiteSetting::get('site_name', env('SITE_NAME')),
            'default_meta_title' => SiteSetting::get('default_meta_title', env('SITE_DEFAULT_META_TITLE')),
            'default_meta_desc' => SiteSetting::get('default_meta_desc', env('SITE_DEFAULT_META_DESC')),
            'default_og_image_path' => SiteSetting::get('default_og_image_path', env('SITE_DEFAULT_OG_IMAGE_PATH')),
            'google_analytics_id' => SiteSetting::get('google_analytics_id', env('GOOGLE_ANALYTICS_ID')),
        ]);
    }

    public function updateSeo(Request $request, \App\Services\MediaUploadService $mediaService)
    {
        $data = $request->validate([
            'site_name' => ['required','string','max:120'],
            'default_meta_title' => ['required','string','max:190'],
            'default_meta_desc' => ['required','string','max:300'],
            'default_og_image_path' => ['nullable'],
            'google_analytics_id' => ['nullable','string','max:50'],
        ]);

        if ($request->hasFile('default_og_image_path')) {
            $oldPath = \App\Models\SiteSetting::get('default_og_image_path');
            if ($oldPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }
            $media = $mediaService->uploadPublicImage($request->file('default_og_image_path'), auth()->id(), 'settings');
            $data['default_og_image_path'] = $media->path;
        } elseif (is_string($request->input('default_og_image_path'))) {
            $data['default_og_image_path'] = $request->input('default_og_image_path');
        } else {
            unset($data['default_og_image_path']); // Keep existing if not changed
        }

        foreach ($data as $k=>$v) SiteSetting::set($k, $v);

        return back()->with('status','SEO global disimpan.');
    }

    public function sitemap()
    {
        return Inertia::render('Admin/Settings/Sitemap', [
            'sitemap_changefreq_default' => SiteSetting::get('sitemap_changefreq_default','daily'),
            'sitemap_priority_default' => SiteSetting::get('sitemap_priority_default','0.5'),
        ]);
    }

    public function updateSitemap(Request $request)
    {
        $data = $request->validate([
            'sitemap_changefreq_default' => ['required','in:hourly,daily,weekly,monthly'],
            'sitemap_priority_default' => ['required','regex:/^\d(\.\d)?$/'],
        ]);

        SiteSetting::set('sitemap_changefreq_default', $data['sitemap_changefreq_default']);
        SiteSetting::set('sitemap_priority_default', $data['sitemap_priority_default']);

        return back()->with('status','Pengaturan sitemap disimpan.');
    }

    public function pages()
    {
        return Inertia::render('Admin/Settings/Pages', [
            'about_title' => SiteSetting::get('about_title', 'Tentang Kami'),
            'about_content' => SiteSetting::get('about_content', SiteSetting::get('about_content_html', '')),
            'about_meta_title' => SiteSetting::get('about_meta_title', 'Tentang Kami - Simbiosis News'),
            'about_meta_desc' => SiteSetting::get('about_meta_desc', 'Informasi tentang redaksi dan misi Simbiosis News.'),

            'contact_title' => SiteSetting::get('contact_title', 'Kontak'),
            'contact_intro' => SiteSetting::get('contact_intro', 'Hubungi redaksi kami melalui formulir atau info kontak berikut.'),
            'contact_address' => SiteSetting::get('contact_address'),
            'contact_phone' => SiteSetting::get('contact_phone'),
            'contact_email_public' => SiteSetting::get('contact_email_public'),
            'contact_hours' => SiteSetting::get('contact_hours'),
            'contact_map_url' => SiteSetting::get('contact_map_url'),
            'contact_recipient_email' => SiteSetting::get('contact_recipient_email'),
            'contact_meta_title' => SiteSetting::get('contact_meta_title', 'Kontak - Simbiosis News'),
            'contact_meta_desc' => SiteSetting::get('contact_meta_desc', 'Hubungi redaksi Simbiosis News.'),
        ]);
    }

    public function updatePages(Request $request)
    {
        $data = $request->validate([
            'about_title' => ['required','string','max:120'],
            'about_content' => ['nullable','string','max:20000'],
            'about_meta_title' => ['required','string','max:190'],
            'about_meta_desc' => ['required','string','max:300'],

            'contact_title' => ['required','string','max:120'],
            'contact_intro' => ['nullable','string','max:500'],
            'contact_address' => ['nullable','string','max:1000'],
            'contact_phone' => ['nullable','string','max:120'],
            'contact_email_public' => ['nullable','email','max:190'],
            'contact_hours' => ['nullable','string','max:300'],
            'contact_map_url' => ['nullable','url','max:500'],
            'contact_recipient_email' => ['nullable','email','max:190'],
            'contact_meta_title' => ['required','string','max:190'],
            'contact_meta_desc' => ['required','string','max:300'],
        ]);

        foreach ($data as $k => $v) {
            SiteSetting::set($k, $v);
        }

        return back()->with('status','Konten halaman publik disimpan.');
    }

    public function aboutEdit()
    {
        $about_title = SiteSetting::get('about_title', 'Tentang Kami');
        $about_meta_title = SiteSetting::get('about_meta_title', 'Tentang Simbiosis News');
        $about_meta_desc = SiteSetting::get('about_meta_desc', 'Menyajikan Jurnalisme Berkualitas untuk Kehidupan yang Lebih Baik.');
        
        $about_hero_headline = SiteSetting::get('about_hero_headline', 'Tentang Simbiosis News');
        $about_hero_subheadline = SiteSetting::get('about_hero_subheadline', 'Menyajikan Jurnalisme Berkualitas untuk Kehidupan yang Lebih Baik.');
        $about_hero_text = SiteSetting::get('about_hero_text', 'Simbiosis News adalah majalah berita digital modern yang menggabungkan kecepatan, akurasi, visual editorial, dan pengalaman baca yang nyaman. Berada di bawah naungan CV. Satu Harmony, kami hadir untuk memberikan perspektif yang jernih di tengah derasnya arus informasi.');
        
        $about_visi = SiteSetting::get('about_visi', 'Menjadi platform media digital referensi utama di Indonesia yang independen, tepercaya, dan berkontribusi dalam mencerdaskan kehidupan bangsa melalui jurnalisme yang berkualitas.');

        // Load JSON lists with fallback defaults
        $misiJson = SiteSetting::get('about_misi_json');
        $misiList = $misiJson ? json_decode($misiJson, true) : [
            'Menyajikan informasi yang akurat, berimbang, dan telah melalui verifikasi ketat.',
            'Menerapkan standar jurnalisme visual modern.',
            'Menjadi ruang diskusi publik yang menjunjung tinggi nilai demokrasi.',
            'Mengedepankan independensi redaksi.'
        ];

        $valuesJson = SiteSetting::get('about_values_json');
        $valuesList = $valuesJson ? json_decode($valuesJson, true) : [
            ['title' => 'Faktual', 'desc' => 'Bersumber dari data dan narasumber kredibel.'],
            ['title' => 'Imparsial', 'desc' => 'Cover both sides secara adil and berimbang.'],
            ['title' => 'Inovatif', 'desc' => 'Adaptasi teknologi untuk pengalaman membaca optimal.']
        ];

        $teamJson = SiteSetting::get('about_team_json');
        $teamList = $teamJson ? json_decode($teamJson, true) : [
            ['name' => 'Aryo Wicaksono', 'role' => 'Pemimpin Umum', 'photo' => null],
            ['name' => 'Diana Permatasari', 'role' => 'Pemimpin Redaksi', 'photo' => null],
            ['name' => 'Budi Santoso', 'role' => 'Redaktur Pelaksana', 'photo' => null],
            ['name' => 'Rina Melati & Antonius Dwi', 'role' => 'Editor Senior', 'photo' => null]
        ];

        $about_publisher = SiteSetting::get('about_publisher', 'CV. Satu Harmony');
        $about_address = SiteSetting::get('about_address', 'Gedung Simbiosis Center, Lt. 3, Jl. Teknologi Raya No. 45, Kebayoran Baru, Jakarta Selatan, DKI Jakarta 12160.');
        $about_email = SiteSetting::get('about_email', 'redaksi@simbiosis.devzoneweb.com');
        $about_phone = SiteSetting::get('about_phone', '+62 812-3456-7890');

        return Inertia::render('Admin/Settings/About', [
            'about_title' => $about_title,
            'about_meta_title' => $about_meta_title,
            'about_meta_desc' => $about_meta_desc,
            'about_hero_headline' => $about_hero_headline,
            'about_hero_subheadline' => $about_hero_subheadline,
            'about_hero_text' => $about_hero_text,
            'about_visi' => $about_visi,
            'misiList' => $misiList,
            'valuesList' => $valuesList,
            'teamList' => $teamList,
            'about_publisher' => $about_publisher,
            'about_address' => $about_address,
            'about_email' => $about_email,
            'about_phone' => $about_phone,
        ]);
    }

    public function updateAbout(Request $request)
    {
        $data = $request->validate([
            'about_title' => ['required','string','max:120'],
            'about_meta_title' => ['required','string','max:190'],
            'about_meta_desc' => ['required','string','max:300'],
            
            'about_hero_headline' => ['required','string','max:190'],
            'about_hero_subheadline' => ['required','string','max:255'],
            'about_hero_text' => ['required','string','max:2000'],
            
            'about_visi' => ['required','string','max:1000'],
            
            // Dynamic arrays
            'misi' => ['nullable','array'],
            'misi.*' => ['nullable','string','max:300'],
            
            'value_titles' => ['nullable','array'],
            'value_titles.*' => ['nullable','string','max:100'],
            'value_descs' => ['nullable','array'],
            'value_descs.*' => ['nullable','string','max:300'],
            
            'team_names' => ['nullable','array'],
            'team_names.*' => ['nullable','string','max:120'],
            'team_roles' => ['nullable','array'],
            'team_roles.*' => ['nullable','string','max:120'],
            'team_photos' => ['nullable','array'],
            'team_photos.*' => ['nullable','string','max:500'],
            
            'about_publisher' => ['required','string','max:190'],
            'about_address' => ['required','string','max:500'],
            'about_email' => ['required','email','max:190'],
            'about_phone' => ['required','string','max:120'],
        ]);

        // Save standard settings
        SiteSetting::set('about_title', $data['about_title']);
        SiteSetting::set('about_meta_title', $data['about_meta_title']);
        SiteSetting::set('about_meta_desc', $data['about_meta_desc']);
        SiteSetting::set('about_hero_headline', $data['about_hero_headline']);
        SiteSetting::set('about_hero_subheadline', $data['about_hero_subheadline']);
        SiteSetting::set('about_hero_text', $data['about_hero_text']);
        SiteSetting::set('about_visi', $data['about_visi']);
        SiteSetting::set('about_publisher', $data['about_publisher']);
        SiteSetting::set('about_address', $data['about_address']);
        SiteSetting::set('about_email', $data['about_email']);
        SiteSetting::set('about_phone', $data['about_phone']);

        // Process Misi JSON list
        $misiList = array_values(array_filter($data['misi'] ?? []));
        SiteSetting::set('about_misi_json', json_encode($misiList));

        // Process Values JSON list
        $values = [];
        $titles = $data['value_titles'] ?? [];
        $descs = $data['value_descs'] ?? [];
        foreach ($titles as $idx => $title) {
            if (!empty($title)) {
                $values[] = [
                    'title' => $title,
                    'desc' => $descs[$idx] ?? ''
                ];
            }
        }
        SiteSetting::set('about_values_json', json_encode($values));

        // Process Team JSON list & files
        $team = [];
        $names = $data['team_names'] ?? [];
        $roles = $data['team_roles'] ?? [];
        $photos = $data['team_photos'] ?? [];

        $oldTeamJson = SiteSetting::get('about_team_json');
        $oldTeam = $oldTeamJson ? json_decode($oldTeamJson, true) : [];
        $oldPhotos = array_filter(array_column($oldTeam, 'photo'));

        // Ensure target folder exists
        $dir = public_path('storage/about');
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach ($names as $idx => $name) {
            if (!empty($name)) {
                $photoPath = $photos[$idx] ?? null;

                if ($photoPath === 'null' || $photoPath === '') {
                    $photoPath = null;
                }

                // Handle file upload
                if ($request->hasFile("team_photo_$idx")) {
                    $file = $request->file("team_photo_$idx");
                    $filename = 'team_' . time() . '_' . $idx . '.' . $file->getClientOriginalExtension();
                    $file->move($dir, $filename);
                    $photoPath = "/storage/about/$filename";
                }

                $team[] = [
                    'name' => $name,
                    'role' => $roles[$idx] ?? '',
                    'photo' => $photoPath
                ];
            }
        }

        // Delete unused old photos
        $newPhotos = array_filter(array_column($team, 'photo'));
        $photosToDelete = array_diff($oldPhotos, $newPhotos);
        foreach ($photosToDelete as $oldPhotoPath) {
            // Remove starting slash if present to correctly resolve public_path
            $relativePath = ltrim($oldPhotoPath, '/');
            $fullPath = public_path($relativePath);
            if (file_exists($fullPath) && is_file($fullPath)) {
                @unlink($fullPath);
            }
        }

        SiteSetting::set('about_team_json', json_encode($team));

        return back()->with('status','Konten Tentang Kami berhasil disimpan.');
    }

    public function social()
    {
        return Inertia::render('Admin/Settings/Social', [
            'social_facebook' => SiteSetting::get('social_facebook'),
            'social_x' => SiteSetting::get('social_x'),
            'social_instagram' => SiteSetting::get('social_instagram'),
            'social_youtube' => SiteSetting::get('social_youtube'),
            'social_linkedin' => SiteSetting::get('social_linkedin'),
            'social_tiktok' => SiteSetting::get('social_tiktok'),
            'social_whatsapp' => SiteSetting::get('social_whatsapp'),
        ]);
    }

    public function updateSocial(Request $request)
    {
        $data = $request->validate([
            'social_facebook' => ['nullable','url','max:500'],
            'social_x' => ['nullable','url','max:500'],
            'social_instagram' => ['nullable','url','max:500'],
            'social_youtube' => ['nullable','url','max:500'],
            'social_linkedin' => ['nullable','url','max:500'],
            'social_tiktok' => ['nullable','url','max:500'],
            'social_whatsapp' => ['nullable','url','max:500'],
        ]);

        foreach ($data as $k => $v) {
            SiteSetting::set($k, $v);
        }

        return back()->with('status', 'Pengaturan social disimpan.');
    }
}
