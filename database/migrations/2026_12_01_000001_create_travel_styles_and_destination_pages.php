<?php

use Database\Seeders\TravelStyleSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Two new header menus with a page for every item:
 *  - Destinations (the existing `experiences`): each gets its own page, so it gains an intro and its own SEO title and description.
 *  - Travel Styles (new): themed ways to see Belgium (medieval cities, art and architecture, food and chocolate, ...), each linked to the
 *    packages that fit it. Edited in Admin > Travel styles; the starter rows are inserted here so an existing database gets them too.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experiences', function (Blueprint $t) {
            $t->text('intro')->nullable()->after('description');
            $t->string('seo_title', 160)->nullable()->after('image_focus');
            $t->string('meta_description', 320)->nullable()->after('seo_title');
        });

        Schema::create('travel_styles', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();   // short line: menu, cards and the hero text
            $t->text('intro')->nullable();          // the paragraph on the style's own page
            $t->string('image')->nullable();
            $t->string('image_alt')->nullable();
            $t->string('image_focus', 40)->nullable();
            $t->string('seo_title', 160)->nullable();
            $t->string('meta_description', 320)->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('package_travel_style', function (Blueprint $t) {
            $t->foreignId('travel_style_id')->constrained()->cascadeOnDelete();
            $t->foreignId('package_id')->constrained()->cascadeOnDelete();
            $t->primary(['travel_style_id', 'package_id']);
        });

        // Starter content for the databases that already exist (the seeder does the same for new ones).
        (new TravelStyleSeeder)->run();

        foreach (TravelStyleSeeder::destinationIntros() as $slug => $intro) {
            DB::table('experiences')->where('slug', $slug)->whereNull('intro')->update(['intro' => $intro]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('package_travel_style');
        Schema::dropIfExists('travel_styles');
        Schema::table('experiences', fn (Blueprint $t) => $t->dropColumn(['intro', 'seo_title', 'meta_description']));
    }
};
