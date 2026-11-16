<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $t) {
            $t->string('route', 160)->nullable()->after('summary'); // e.g. "Bruges · Ghent", shown on journey cards
        });

        Schema::table('tour_types', function (Blueprint $t) {
            $t->string('tab_label', 40)->nullable()->after('name'); // short tab text, e.g. "Self-Drive"
        });
    }

    public function down(): void
    {
        Schema::table('packages', fn (Blueprint $t) => $t->dropColumn('route'));
        Schema::table('tour_types', fn (Blueprint $t) => $t->dropColumn('tab_label'));
    }
};
