<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Optional "was" price shown crossed out beside the current price on package cards. Blank = no strikethrough. Never seeded: no invented prices. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $t) {
            $t->decimal('original_price', 10, 2)->nullable()->after('price_from');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $t) {
            $t->dropColumn('original_price');
        });
    }
};