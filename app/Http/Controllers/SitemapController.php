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
            [route('travel-styles.index'), now()],
            [route('journal.index'), now()],
            [route('faqs'), now()],
        ];

        // Sample (placeholder) team and testimonials stay out of the sitemap until real ones exist.
        if (\App\Models\TeamMember::active()->where('is_placeholder', false)->exists() && ! Page::bySlug('team')?->noindex) {
            $urls[] = [route('team'), now()];
        }
        if (\App\Models\Testimonial::active()->where('is_placeholder', false)->exists() && ! Page::bySlug('testimonials')?->noindex) {
            $urls[] = [route('testimonials'), now()];
        }

        foreach (\App\Models\TourType::active()->get() as $t) {
            $urls[] = [route('tours.show', $t->slug), $t->updated_at];
        }

        foreach (\App\Models\Experience::active()->get() as $d) {
            $urls[] = [route('destinations.show', $d->slug), $d->updated_at];
        }
        foreach (\App\Models\TravelStyle::active()->get() as $s) {
            $urls[] = [route('travel-styles.show', $s->slug), $s->updated_at];
        }

        foreach (\App\Models\Post::published()->where('is_placeholder', false)->get() as $post) {
            $urls[] = [route('journal.show', $post->slug), $post->updated_at];
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
