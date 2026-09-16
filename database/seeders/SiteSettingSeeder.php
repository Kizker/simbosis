<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::set('comments_enabled', '1');

        SiteSetting::set('site_name', env('SITE_NAME', 'Simbiosis News'));
        SiteSetting::set('default_meta_title', env('SITE_DEFAULT_META_TITLE', 'Simbiosis News - Majalah Berita Modern'));
        SiteSetting::set('default_meta_desc', env('SITE_DEFAULT_META_DESC', 'News magazine modern dengan kurasi berita aktual, visual editorial, dan pengalaman baca yang rapi.'));
        SiteSetting::set('default_og_image_path', env('SITE_DEFAULT_OG_IMAGE_PATH', null));

        SiteSetting::set('social_facebook', null);
        SiteSetting::set('social_x', null);
        SiteSetting::set('social_instagram', null);
        SiteSetting::set('social_youtube', null);
        SiteSetting::set('social_linkedin', null);
        SiteSetting::set('social_tiktok', null);
        SiteSetting::set('social_whatsapp', null);

        SiteSetting::set('sitemap_changefreq_default', 'daily');
        SiteSetting::set('sitemap_priority_default', '0.5');

        SiteSetting::set('about_title', 'Tentang Kami');
        SiteSetting::set('about_content', 'Simbiosis News adalah majalah berita digital modern yang menggabungkan kecepatan, akurasi, visual editorial, dan pengalaman baca yang nyaman.');
        SiteSetting::set('about_meta_title', 'Tentang Kami - Simbiosis News');
        SiteSetting::set('about_meta_desc', 'Informasi tentang redaksi dan misi Simbiosis News.');

        SiteSetting::set('contact_title', 'Kontak');
        SiteSetting::set('contact_intro', 'Hubungi redaksi kami melalui formulir atau info kontak berikut.');
        SiteSetting::set('contact_address', null);
        SiteSetting::set('contact_phone', null);
        SiteSetting::set('contact_email_public', null);
        SiteSetting::set('contact_hours', null);
        SiteSetting::set('contact_map_url', null);
        SiteSetting::set('contact_recipient_email', null);
        SiteSetting::set('contact_meta_title', 'Kontak - Simbiosis News');
        SiteSetting::set('contact_meta_desc', 'Hubungi redaksi Simbiosis News.');
    }
}
