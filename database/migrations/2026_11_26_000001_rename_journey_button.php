<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** The Journey section button now reads "Plan my journey now". Only the old starter wording is replaced; anything edited in the admin is kept. */
return new class extends Migration
{
    public function up(): void
    {
        $this->swap('Explore Destinations', 'Plan my journey now');
    }

    public function down(): void
    {
        $this->swap('Plan my journey now', 'Explore Destinations');
    }

    private function swap(string $from, string $to): void
    {
        $row = DB::table('pages')->where('slug', 'home')->first();
        if (! $row) {
            return;
        }
        $content = json_decode((string) $row->content, true) ?: [];
        if (($content['journey_cta'] ?? null) === $from) {
            $content['journey_cta'] = $to;
            DB::table('pages')->where('id', $row->id)->update(['content' => json_encode($content, JSON_UNESCAPED_UNICODE), 'updated_at' => now()]);
        }
    }
};
