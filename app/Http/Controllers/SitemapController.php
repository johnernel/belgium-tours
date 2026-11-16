<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Page;

class SitemapController extends Controller
{
    public function sitemap()
    {
        $urls = [
            [route('home'), now()],
            [route('packages.index'), now()],
            [route('destinations.index'), now()],
        ];

        foreach (\App\Models\TourType::active()->get() as $t) {
            $urls[] = [route('tours.show', $t->slug), $t->updated_at];
        }

        foreach (Page::where('noindex', false)->whereIn('slug', ['about', 'contact', 'terms', 'privacy'])->get() as $p) {
            $urls[] = [route($p->slug), $p->updated_at];
        }

        foreach (Package::published()->where('noindex', false)->get() as $pkg) {
            $urls[] = [route('packages.show', $pkg->slug), $pkg->updated_at];
        }

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }

    public function robots()
    {
        $body = "User-agent: *\nDisallow: /admin\nDisallow: /thank-you\nAllow: /\n\nSitemap: ".url('/sitemap.xml')."\n";

        return response($body)->header('Content-Type', 'text/plain');
    }
}
