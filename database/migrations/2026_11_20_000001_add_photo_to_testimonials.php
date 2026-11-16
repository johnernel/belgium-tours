<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $t) {
            $t->string('photo', 255)->nullable()->after('quote');
            $t->string('photo_alt', 200)->nullable()->after('photo');
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', fn (Blueprint $t) => $t->dropColumn(['photo', 'photo_alt']));
    }
};
