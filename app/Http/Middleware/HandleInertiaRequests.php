<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Category;
use App\Models\SiteSetting;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        $settings = [
            'site_name' => SiteSetting::get('site_name', env('SITE_NAME', 'Simbiosis News')),
            'default_meta_title' => SiteSetting::get('default_meta_title', env('SITE_DEFAULT_META_TITLE')),
            'default_meta_desc' => SiteSetting::get('default_meta_desc', env('SITE_DEFAULT_META_DESC')),
            'default_og_image_path' => SiteSetting::get('default_og_image_path', env('SITE_DEFAULT_OG_IMAGE_PATH')),
            'social_facebook' => SiteSetting::get('social_facebook'),
            'social_instagram' => SiteSetting::get('social_instagram'),
            'social_youtube' => SiteSetting::get('social_youtube'),
        ];

        return array_merge(parent::share($request), [
            'appName' => $settings['site_name'],
            'metaTitle' => $settings['default_meta_title'],
            'metaDescription' => $settings['default_meta_desc'],
            'ogImage' => $settings['default_og_image_path'],
            'social' => [
                'facebook' => $settings['social_facebook'],
                'instagram' => $settings['social_instagram'],
                'youtube' => $settings['social_youtube'],
            ],
            'headerCategories' => Category::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->getRoleNames()->first(),
                    'permissions' => $request->user()->getAllPermissions()->pluck('name')->values()->all(),
                ] : null,
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'ads' => \Illuminate\Support\Facades\Cache::remember('global:ads', now()->addMinutes(5), function () {
                return \App\Models\AdsBanner::where('is_active', true)->get()->groupBy('slot');
            }),
        ]);
    }
}
