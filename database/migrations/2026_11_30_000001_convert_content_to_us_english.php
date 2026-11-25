<?php

use App\Support\UsEnglish;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The site's audience is in the United States: convert the visible text stored in the database (pages, packages, FAQs, testimonials,
 * quiz questions and answer labels, tour types, destinations, journal, team, a few settings) from UK to US spelling and wording
 * (enquiry to inquiry, travellers to travelers, autumn to fall, hire car to rental car, holiday to vacation, and so on).
 *
 * Only the words in App\Support\UsEnglish are touched, only inside the text columns listed here, and never slugs, keys, quiz answer
 * VALUES, image paths, URLs, e-mail addresses or anything that looks like a code word, so integrations keep working.
 * Not reversible on purpose: the old spellings are not wanted back (down() does nothing).
 */
return new class extends Migration
{
    /** table => [primary key column, [text / json columns to convert]] */
    private const TARGETS = [
        'pages' => ['id', ['title', 'body', 'content', 'seo_title', 'meta_description']],
        'packages' => ['id', ['title', 'summary', 'route', 'description', 'highlights', 'itinerary', 'inclusions', 'exclusions', 'faqs', 'hero_image_alt', 'seo_title', 'meta_description']],
        'faqs' => ['id', ['question', 'answer']],
        'testimonials' => ['id', ['location', 'quote', 'photo_alt']],
        'quiz_steps' => ['id', ['question']],
        'quiz_options' => ['id', ['label']],
        'tour_types' => ['id', ['name', 'tab_label', 'headline', 'summary', 'menu_text', 'intro', 'image_alt', 'seo_title', 'meta_description']],
        'experiences' => ['id', ['description', 'image_alt']],
        'posts' => ['id', ['title', 'category', 'excerpt', 'body', 'image_alt']],
        'team_members' => ['id', ['role', 'bio', 'photo_alt']],
        'package_images' => ['id', ['alt']],
    ];

    /** settings are key/value: only these keys hold visible text */
    private const SETTING_KEYS = ['tagline', 'default_meta_description', 'footer_text', 'quote_discount_text'];

    public function up(): void
    {
        foreach (self::TARGETS as $table => [$pk, $columns]) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            $columns = array_values(array_filter($columns, fn ($c) => Schema::hasColumn($table, $c)));
            if (! $columns) {
                continue;
            }
            foreach (DB::table($table)->select(array_merge([$pk], $columns))->get() as $row) {
                $changes = [];
                foreach ($columns as $c) {
                    $old = $row->$c;
                    if (! is_string($old) || $old === '') {
                        continue;
                    }
                    $new = UsEnglish::convertValue($old);
                    if (is_string($new) && $new !== $old) {
                        $changes[$c] = $new;
                    }
                }
                if ($changes) {
                    DB::table($table)->where($pk, $row->$pk)->update($changes);
                }
            }
        }

        if (Schema::hasTable('settings')) {
            foreach (DB::table('settings')->whereIn('key', self::SETTING_KEYS)->get() as $row) {
                $new = is_string($row->value) ? UsEnglish::convert($row->value) : $row->value;
                if ($new !== $row->value) {
                    DB::table('settings')->where('key', $row->key)->update(['value' => $new]);
                }
            }
        }
    }

    public function down(): void
    {
        // Intentionally empty.
    }
};
