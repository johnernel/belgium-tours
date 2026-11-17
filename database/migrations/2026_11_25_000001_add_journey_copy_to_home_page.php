<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Adds the editable wording of the home page "The Journey Never Ends" section (only the keys that are still missing). */
return new class extends Migration
{
    private array $keys = [
        'journey_title_1' => 'The Journey',
        'journey_title_2' => 'Never Ends',
        'journey_text' => 'From hidden canals to medieval towns, every journey adds a new chapter to your story.',
        'journey_cta' => 'Explore Destinations',
    ];

    public function up(): void
    {
        $row = DB::table('pages')->where('slug', 'home')->first();
        if (! $row) {
            return;
        }
        $content = json_decode((string) $row->content, true) ?: [];
        foreach ($this->keys as $k => $v) {
            if (blank($content[$k] ?? null)) {
                $content[$k] = $v;
            }
        }
        DB::table('pages')->where('id', $row->id)->update(['content' => json_encode($content, JSON_UNESCAPED_UNICODE), 'updated_at' => now()]);
    }

    public function down(): void
    {
        $row = DB::table('pages')->where('slug', 'home')->first();
        if ($row) {
            $content = json_decode((string) $row->content, true) ?: [];
            foreach (array_keys($this->keys) as $k) {
                unset($content[$k]);
            }
            DB::table('pages')->where('id', $row->id)->update(['content' => json_encode($content, JSON_UNESCAPED_UNICODE)]);
        }
    }
};
