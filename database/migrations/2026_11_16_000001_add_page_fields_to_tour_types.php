<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_types', function (Blueprint $t) {
            $t->string('headline')->nullable()->after('slug');       // H1 on the tour type page
            $t->text('intro')->nullable()->after('summary');         // short description beside the quiz
            $t->string('seo_title')->nullable();
            $t->string('meta_description', 320)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tour_types', function (Blueprint $t) {
            $t->dropColumn(['headline', 'intro', 'seo_title', 'meta_description']);
        });
    }
};
