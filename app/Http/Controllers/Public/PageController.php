<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class PageController extends Controller
{
    public function about()
    {
        $title = SiteSetting::get('about_title', 'Tentang Kami');
        $metaTitle = SiteSetting::get('about_meta_title', 'Tentang Simbiosis News');
        $metaDescription = SiteSetting::get('about_meta_desc', 'Menyajikan Jurnalisme Berkualitas untuk Kehidupan yang Lebih Baik.');

        // Hero Section
        $heroHeadline = SiteSetting::get('about_hero_headline', 'Tentang Simbiosis News');
        $heroSubheadline = SiteSetting::get('about_hero_subheadline', 'Menyajikan Jurnalisme Berkualitas untuk Kehidupan yang Lebih Baik.');
        $heroText = SiteSetting::get('about_hero_text', 'Simbiosis News adalah majalah berita digital modern yang menggabungkan kecepatan, akurasi, visual editorial, dan pengalaman baca yang nyaman. Berada di bawah naungan CV. Satu Harmony, kami hadir untuk memberikan perspektif yang jernih di tengah derasnya arus informasi.');

        // Visi
        $visi = SiteSetting::get('about_visi', 'Menjadi platform media digital referensi utama di Indonesia yang independen, tepercaya, dan berkontribusi dalam mencerdaskan kehidupan bangsa melalui jurnalisme yang berkualitas.');

        // Dynamic Missions List
        $misiJson = SiteSetting::get('about_misi_json');
        $misiList = $misiJson ? json_decode($misiJson, true) : [
            'Menyajikan informasi yang akurat, berimbang, dan telah melalui verifikasi ketat.',
            'Menerapkan standar jurnalisme visual modern.',
            'Menjadi ruang diskusi publik yang menjunjung tinggi nilai demokrasi.',
            'Mengedepankan independensi redaksi.'
        ];

        // Dynamic Core Values List
        $valuesJson = SiteSetting::get('about_values_json');
        $valuesList = $valuesJson ? json_decode($valuesJson, true) : [
            ['title' => 'Faktual', 'desc' => 'Bersumber dari data dan narasumber kredibel.'],
            ['title' => 'Imparsial', 'desc' => 'Cover both sides secara adil dan berimbang.'],
            ['title' => 'Inovatif', 'desc' => 'Adaptasi teknologi untuk pengalaman membaca optimal.']
        ];

        // Dynamic Team Members List from Settings
        $teamJson = SiteSetting::get('about_team_json');
        $teamList = $teamJson ? json_decode($teamJson, true) : [
            ['name' => 'Aryo Wicaksono', 'role' => 'Pemimpin Umum', 'photo' => null],
            ['name' => 'Diana Permatasari', 'role' => 'Pemimpin Redaksi', 'photo' => null],
            ['name' => 'Budi Santoso', 'role' => 'Redaktur Pelaksana', 'photo' => null],
            ['name' => 'Rina Melati & Antonius Dwi', 'role' => 'Editor Senior', 'photo' => null]
        ];

        // Publisher & Contact
        $publisher = SiteSetting::get('about_publisher', 'CV. Satu Harmony');
        $address = SiteSetting::get('about_address', 'Gedung Simbiosis Center, Lt. 3, Jl. Teknologi Raya No. 45, Kebayoran Baru, Jakarta Selatan, DKI Jakarta 12160.');
        $email = SiteSetting::get('about_email', 'redaksi@simbiosis.devzoneweb.com');
        $phone = SiteSetting::get('about_phone', '+62 812-3456-7890');

        return Inertia::render('Public/About', compact(
            'title', 'metaTitle', 'metaDescription',
            'heroHeadline', 'heroSubheadline', 'heroText',
            'visi', 'misiList', 'valuesList', 'teamList',
            'publisher', 'address', 'email', 'phone'
        ));
    }

    public function contact()
    {
        $title = SiteSetting::get('contact_title', 'Kontak');
        $intro = SiteSetting::get('contact_intro', 'Hubungi redaksi kami melalui formulir atau info kontak berikut.');
        $address = SiteSetting::get('contact_address');
        $phone = SiteSetting::get('contact_phone');
        $emailPublic = SiteSetting::get('contact_email_public');
        $hours = SiteSetting::get('contact_hours');
        $mapUrl = SiteSetting::get('contact_map_url');
        $metaTitle = SiteSetting::get('contact_meta_title', 'Kontak - Simbiosis News');
        $metaDescription = SiteSetting::get('contact_meta_desc', 'Hubungi redaksi Simbiosis News.');

        return Inertia::render('Public/Contact', compact(
            'title',
            'intro',
            'address',
            'phone',
            'emailPublic',
            'hours',
            'mapUrl',
            'metaTitle',
            'metaDescription'
        ));
    }

    public function sendContact(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'email' => ['required','email','max:190'],
            'message' => ['required','string','max:2000'],
        ]);

        $to = SiteSetting::get('contact_recipient_email') ?: config('mail.from.address');

        ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'message' => $data['message'],
        ]);

        try {
            Mail::raw("From: {$data['name']} <{$data['email']}>\n\n{$data['message']}", function ($m) use ($to) {
                $m->to($to)->subject('[Kontak] Simbiosis News');
            });
        } catch (\Exception $e) {
            // Log if email fails, but continue to avoid showing error to user since DB save succeeded
            \Illuminate\Support\Facades\Log::error('Failed to send contact notification: ' . $e->getMessage());
        }

        return back()->with('status', 'Pesan terkirim.');
    }
}
