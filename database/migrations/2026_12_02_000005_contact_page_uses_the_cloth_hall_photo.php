<?php

use App\Support\InfoPages;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * New banner photo for the Contact page (the cloth hall photo supplied by the client).
 * Only a blank or starter photo is replaced: a photo someone uploaded in Admin > Pages is left alone. Not reversible.
 */
return new class extends Migration
{
    public function up(): void
    {
        $page = Schema::hasTable('pages') ? DB::table('pages')->where('slug', 'contact')->first() : null;
        if (! $page) {
            return;
        }

        $content = json_decode((string) $page->content, true) ?: [];
        if (in_array($content['hero_image'] ?? '', ['', 'images/photos/brussels-grand-place'], true)) {
            [$content['hero_image'], $content['hero_image_alt']] = InfoPages::BANNERS['contact'];
            DB::table('pages')->where('slug', 'contact')->update(['content' => json_encode($content)]);
        }
    }

    public function down(): void
    {
        // Intentionally empty.
    }
};
