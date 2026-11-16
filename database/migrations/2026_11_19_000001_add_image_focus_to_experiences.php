<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experiences', function (Blueprint $t) {
            $t->string('image_focus', 40)->nullable()->after('image_alt'); // CSS object-position, e.g. "32% 50%"
        });
    }

    public function down(): void
    {
        Schema::table('experiences', fn (Blueprint $t) => $t->dropColumn('image_focus'));
    }
};
