<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** A one-line blurb per tour type for the Tour Types mega menu (the longer card summary does not fit on one line). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_types', function (Blueprint $t) {
            $t->string('menu_text', 60)->nullable()->after('summary');
        });

        $starter = [
            'self-drive' => 'Your own pace, your own route.',
            'chauffeur-driven' => 'A private driver-guide at the wheel.',
            'coach-tours' => 'Small-group touring, no driving.',
        ];
        foreach ($starter as $slug => $text) {
            DB::table('tour_types')->where('slug', $slug)->whereNull('menu_text')->update(['menu_text' => $text]);
        }
    }

    public function down(): void
    {
        Schema::table('tour_types', fn (Blueprint $t) => $t->dropColumn('menu_text'));
    }
};
