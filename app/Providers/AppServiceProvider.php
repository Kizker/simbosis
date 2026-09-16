<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use App\Models\Category;
use App\Models\SiteSetting;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        RateLimiter::for('comment', fn (Request $r) => Limit::perMinute(10)->by($r->ip()));
        RateLimiter::for('upload', fn (Request $r) => Limit::perMinute(20)->by((optional($r->user())->id ?? 'guest').'|'.$r->ip()));
        RateLimiter::for('publish', fn (Request $r) => Limit::perMinute(30)->by(optional($r->user())->id ?? 'guest'));
        RateLimiter::for('share', fn (Request $r) => Limit::perMinute(60)->by($r->ip()));
        RateLimiter::for('contact', fn (Request $r) => Limit::perMinute(5)->by($r->ip()));

        View::composer('*', function ($view) {
            $settings = [
                'site_name' => SiteSetting::get('site_name', env('SITE_NAME', 'Simbiosis News')),
                'default_meta_title' => SiteSetting::get('default_meta_title', env('SITE_DEFAULT_META_TITLE')),
                'default_meta_desc' => SiteSetting::get('default_meta_desc', env('SITE_DEFAULT_META_DESC')),
                'default_og_image_path' => SiteSetting::get('default_og_image_path', env('SITE_DEFAULT_OG_IMAGE_PATH')),
                'ga_id' => SiteSetting::get('google_analytics_id', env('GOOGLE_ANALYTICS_ID')),
                'comments_enabled' => SiteSetting::get('comments_enabled', '1'),
                'social_facebook' => SiteSetting::get('social_facebook'),
                'social_x' => SiteSetting::get('social_x'),
                'social_instagram' => SiteSetting::get('social_instagram'),
                'social_youtube' => SiteSetting::get('social_youtube'),
                'social_linkedin' => SiteSetting::get('social_linkedin'),
                'social_tiktok' => SiteSetting::get('social_tiktok'),
                'social_whatsapp' => SiteSetting::get('social_whatsapp'),
            ];

            $view->with('siteSettings', $settings);
            $view->with([
                'siteName' => $settings['site_name'],
                'metaTitle' => $settings['default_meta_title'],
                'metaDescription' => $settings['default_meta_desc'],
                'ogImage' => $settings['default_og_image_path'],
                'googleAnalyticsId' => $settings['ga_id'],
                'commentsEnabled' => (string) $settings['comments_enabled'] === '1',
                'socialFacebook' => $settings['social_facebook'],
                'socialX' => $settings['social_x'],
                'socialInstagram' => $settings['social_instagram'],
                'socialYoutube' => $settings['social_youtube'],
                'socialLinkedin' => $settings['social_linkedin'],
                'socialTiktok' => $settings['social_tiktok'],
                'socialWhatsapp' => $settings['social_whatsapp'],
                'headerCategories' => Category::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug']),
            ]);
        });
    }
}
