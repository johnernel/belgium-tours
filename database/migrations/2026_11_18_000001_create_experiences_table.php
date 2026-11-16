<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // "Top destinations": themed places to go (chocolate, beer and abbeys, ...). Packages can belong to several.
        Schema::create('experiences', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->string('image')->nullable();
            $t->string('image_alt')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('experience_package', function (Blueprint $t) {
            $t->foreignId('experience_id')->constrained()->cascadeOnDelete();
            $t->foreignId('package_id')->constrained()->cascadeOnDelete();
            $t->primary(['experience_id', 'package_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experience_package');
        Schema::dropIfExists('experiences');
    }
};
