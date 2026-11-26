<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The client confirmed that "Go Belgium Trips" is the actual company name, so the legal company name (consent wording, legal pages, footer
 * copyright, structured data) changes from "Go Belgium Tours". Only a value that still holds the old default is replaced: a name someone typed
 * in Admin > Settings is left alone. The consent wording therefore changes, and `terms_version` in config/gotour.php was bumped with it.
 * Not reversible (down() does nothing).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            DB::table('settings')->where('key', 'legal_company_name')->where('value', 'Go Belgium Tours')->update(['value' => 'Go Belgium Trips']);
        }
    }

    public function down(): void
    {
        // Intentionally empty.
    }
};