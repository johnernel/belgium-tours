<?php

use App\Support\InfoPages;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Team members, plus the Meet Our Team / Testimonials / FAQs page records (only created when missing). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $t) {
            $t->id();
            $t->string('name', 120);
            $t->string('role', 120)->nullable();
            $t->text('bio')->nullable();
            $t->string('photo', 255)->nullable();
            $t->string('photo_alt', 200)->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('is_placeholder')->default(false);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        $now = now();
        foreach (InfoPages::pages() as $slug => $p) {
            if (DB::table('pages')->where('slug', $slug)->exists()) {
                continue;
            }
            DB::table('pages')->insert([
                'slug' => $slug, 'title' => $p['title'], 'body' => null, 'content' => json_encode($p['content'], JSON_UNESCAPED_UNICODE),
                'seo_title' => $p['seo_title'], 'meta_description' => $p['meta'], 'noindex' => $p['noindex'],
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        foreach (InfoPages::teamMembers() as $m) {
            DB::table('team_members')->insert($m + ['created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
        DB::table('pages')->whereIn('slug', array_keys(InfoPages::pages()))->delete();
    }
};
