<?php

use App\Support\InfoPages;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The banner photo of About Us, Meet Our Team, Testimonials and FAQs now matches that page's photo in the About Us menu.
 * Only a blank or starter photo is replaced: a photo someone uploaded in Admin > Pages is left alone. Not reversible.
 */
return new class extends Migration
{
    private const STARTER_PHOTOS = [
        'about' => 'images/photos/gravensteen',
        'team' => 'images/photos/countryside',
        'testimonials' => 'images/photos/ghent-graslei',
        'faqs' => 'images/photos/city-leuven',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        foreach (self::STARTER_PHOTOS as $slug => $starter) {
            $page = DB::table('pages')->where('slug', $slug)->first();
            if (! $page) {
                continue;
            }

            $content = json_decode((string) $page->content, true) ?: [];
            if (! in_array($content['hero_image'] ?? '', ['', $starter], true)) {
                continue;
            }

            [$content['hero_image'], $content['hero_image_alt']] = InfoPages::BANNERS[$slug];
            DB::table('pages')->where('slug', $slug)->update(['content' => json_encode($content)]);
        }
    }

    public function down(): void
    {
        // Intentionally empty.
    }
};
