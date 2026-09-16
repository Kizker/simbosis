<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $xml = Cache::remember('public:sitemap', now()->addMinutes(30), function () {
            $base = rtrim(config('app.url'), '/');

            $urls = [];
            $urls[] = ['loc' => $base.'/', 'changefreq' => 'hourly', 'priority' => '1.0'];
            $urls[] = ['loc' => $base.'/trending', 'changefreq' => 'hourly', 'priority' => '0.9'];
            $urls[] = ['loc' => $base.'/video', 'changefreq' => 'daily', 'priority' => '0.6'];
            $urls[] = ['loc' => $base.'/foto', 'changefreq' => 'daily', 'priority' => '0.6'];
            $urls[] = ['loc' => $base.'/tentang-kami', 'changefreq' => 'monthly', 'priority' => '0.3'];
            $urls[] = ['loc' => $base.'/kontak', 'changefreq' => 'monthly', 'priority' => '0.3'];

            foreach (Category::query()->where('is_active', true)->get(['slug','updated_at']) as $c) {
                $urls[] = ['loc' => $base.'/kategori/'.$c->slug, 'changefreq' => 'daily', 'priority' => '0.6', 'lastmod' => $c->updated_at?->toAtomString()];
            }
            foreach (Tag::query()->get(['slug','updated_at']) as $t) {
                $urls[] = ['loc' => $base.'/tag/'.$t->slug, 'changefreq' => 'weekly', 'priority' => '0.4', 'lastmod' => $t->updated_at?->toAtomString()];
            }
            foreach (User::query()->has('authorProfile')->get(['id','updated_at']) as $u) {
                $urls[] = ['loc' => $base.'/penulis/'.$u->id, 'changefreq' => 'weekly', 'priority' => '0.4', 'lastmod' => $u->updated_at?->toAtomString()];
            }
            foreach (Article::query()->published()->latest('published_at')->get(['slug','updated_at']) as $a) {
                $urls[] = ['loc' => $base.'/artikel/'.$a->slug, 'changefreq' => 'daily', 'priority' => '0.8', 'lastmod' => $a->updated_at?->toAtomString()];
            }

            $out = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
            foreach ($urls as $u) {
                $out .= "  <url>\n";
                $out .= "    <loc>".e($u['loc'])."</loc>\n";
                if (!empty($u['lastmod'])) $out .= "    <lastmod>{$u['lastmod']}</lastmod>\n";
                $out .= "    <changefreq>{$u['changefreq']}</changefreq>\n";
                $out .= "    <priority>{$u['priority']}</priority>\n";
                $out .= "  </url>\n";
            }
            $out .= '</urlset>';
            return $out;
        });

        return Response::make($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }
}
