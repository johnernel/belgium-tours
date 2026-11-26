<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The brand is "Go Belgium Trips" (the logo, the domain and the admin already said so): replace the old brand phrase "Go Belgium Tours"
 * in the saved brand name setting and in the visible text stored in the database (page titles, search titles and descriptions, copy).
 *
 * NOT touched on purpose: `legal_company_name`. It is the company name used in the consent wording on every form, in the legal pages and in the
 * footer copyright line, so it is changed by hand in Admin > Settings (and the consent wording version bumped) once the client confirms the legal name.
 * Not reversible (down() does nothing).
 */
return new class extends Migration
{
    private const OLD = 'Go Belgium Tours';

    private const NEW = 'Go Belgium Trips';

    /** table => [primary key, text columns] */
    private const TARGETS = [
        'pages' => ['id', ['title', 'body', 'content', 'seo_title', 'meta_description']],
        'tour_types' => ['id', ['headline', 'summary', 'menu_text', 'intro', 'seo_title', 'meta_description']],
        'packages' => ['id', ['summary', 'description', 'seo_title', 'meta_description']],
        'faqs' => ['id', ['question', 'answer']],
        'testimonials' => ['id', ['quote']],
        'posts' => ['id', ['title', 'excerpt', 'body']],
        'experiences' => ['id', ['description', 'intro', 'seo_title', 'meta_description']],
        'travel_styles' => ['id', ['description', 'intro', 'seo_title', 'meta_description']],
    ];

    /** settings keys that hold visible text (brand_name is replaced exactly; the legal company name is left alone) */
    private const SETTING_KEYS = ['brand_name', 'tagline', 'default_meta_description', 'footer_text', 'quote_discount_text'];

    public function up(): void
    {
        foreach (self::TARGETS as $table => [$pk, $columns]) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            $columns = array_values(array_filter($columns, fn ($c) => Schema::hasColumn($table, $c)));
            foreach (DB::table($table)->select(array_merge([$pk], $columns))->get() as $row) {
                $changes = [];
                foreach ($columns as $c) {
                    if (is_string($row->$c) && str_contains($row->$c, self::OLD)) {
                        // json columns are stored as text: the phrase has no characters that JSON escapes, so a plain replace is safe
                        $changes[$c] = str_replace(self::OLD, self::NEW, $row->$c);
                    }
                }
                if ($changes) {
                    DB::table($table)->where($pk, $row->$pk)->update($changes);
                }
            }
        }

        if (Schema::hasTable('settings')) {
            foreach (DB::table('settings')->whereIn('key', self::SETTING_KEYS)->get() as $row) {
                if (is_string($row->value) && str_contains($row->value, self::OLD)) {
                    DB::table('settings')->where('key', $row->key)->update(['value' => str_replace(self::OLD, self::NEW, $row->value)]);
                }
            }
        }
    }

    public function down(): void
    {
        // Intentionally empty.
    }
};
