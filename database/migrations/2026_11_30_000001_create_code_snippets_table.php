<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Admin > Header & Footer: custom code snippets (tracking pixels, scripts, styles, any HTML) output on the public site. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('code_snippets', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('type', 10)->default('script');          // script | style | html
            $t->string('placement', 12)->default('head');       // head | body_start | body_end
            $t->longText('code');
            $t->string('scope', 8)->default('all');              // all | only | except
            $t->json('pages')->nullable();                       // paths, "*" wildcard allowed
            $t->unsignedInteger('sort')->default(1);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('code_snippets');
    }
};