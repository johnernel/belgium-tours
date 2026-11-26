<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The home page's browser tab started with "Belgium Tours: ..." and only ended with the brand. It now starts with the brand name.
 * Only the saved starter title is replaced: a title someone wrote in Admin > Pages > Home is left alone. Not reversible.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pages')) {
            DB::table('pages')->where('slug', 'home')
                ->whereIn('seo_title', ['Belgium Tours: Self Drive, Chauffeur Driven & Coach | Go Belgium Trips', 'Belgium Tours: Self Drive, Chauffeur Driven & Coach | Go Belgium Tours'])
                ->update(['seo_title' => 'Go Belgium Trips: Self Drive, Chauffeur Driven & Coach Tours']);
        }
    }

    public function down(): void
    {
        // Intentionally empty.
    }
};