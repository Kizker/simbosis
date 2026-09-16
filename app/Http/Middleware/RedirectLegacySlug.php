<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SiteSetting;

class RedirectLegacySlug
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = '/'.$request->path();
        $redirects = json_decode((string) SiteSetting::get('slug_redirects', '{}'), true) ?: [];

        if (isset($redirects[$path])) {
            return redirect($redirects[$path], 301);
        }

        return $next($request);
    }
}
