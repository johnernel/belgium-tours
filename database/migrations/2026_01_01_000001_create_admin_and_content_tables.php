<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->string('password');
            $t->rememberToken();
            $t->timestamps();
        });

        Schema::create('settings', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->longText('value')->nullable();
            $t->timestamps();
        });

        Schema::create('tour_types', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('summary')->nullable();
            $t->string('image')->nullable();
            $t->string('image_alt')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('destinations', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('packages', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->foreignId('tour_type_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedSmallInteger('nights')->default(0);
            $t->decimal('price_from', 10, 2)->nullable();
            $t->string('currency', 3)->default('EUR');
            $t->text('summary')->nullable();
            $t->longText('description')->nullable();
            $t->json('highlights')->nullable();
            $t->json('itinerary')->nullable();
            $t->json('inclusions')->nullable();
            $t->json('exclusions')->nullable();
            $t->json('faqs')->nullable();
            $t->string('hero_image')->nullable();
            $t->string('hero_image_alt')->nullable();
            $t->string('seo_title')->nullable();
            $t->string('meta_description', 320)->nullable();
            $t->string('social_image')->nullable();
            $t->boolean('noindex')->default(false);
            $t->boolean('is_published')->default(false);
            $t->boolean('is_featured')->default(false);
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('package_images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('package_id')->constrained()->cascadeOnDelete();
            $t->string('path');
            $t->string('alt');
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('pages', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('title');
            $t->longText('body')->nullable();
            $t->json('content')->nullable();
            $t->string('seo_title')->nullable();
            $t->string('meta_description', 320)->nullable();
            $t->boolean('noindex')->default(false);
            $t->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('location')->nullable();
            $t->text('quote');
            $t->boolean('is_placeholder')->default(true);
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('faqs', function (Blueprint $t) {
            $t->id();
            $t->string('question');
            $t->text('answer');
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('media', function (Blueprint $t) {
            $t->id();
            $t->string('path');
            $t->string('alt')->nullable();
            $t->unsignedInteger('width')->nullable();
            $t->unsignedInteger('height')->nullable();
            $t->json('variants')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['media','faqs','testimonials','pages','package_images','packages','destinations','tour_types','settings','admin_users'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
