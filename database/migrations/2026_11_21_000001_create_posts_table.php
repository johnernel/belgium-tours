<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $t) {
            $t->id();
            $t->string('title', 160);
            $t->string('slug', 160)->unique();
            $t->string('category', 60)->nullable();
            $t->string('excerpt', 500)->nullable();
            $t->longText('body')->nullable();
            $t->string('image', 255)->nullable();
            $t->string('image_alt', 200)->nullable();
            $t->unsignedSmallInteger('read_minutes')->default(4);
            $t->timestamp('published_at')->nullable();
            $t->boolean('is_placeholder')->default(false);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
