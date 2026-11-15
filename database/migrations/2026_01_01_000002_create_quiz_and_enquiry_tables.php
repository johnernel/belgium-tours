<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_steps', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();          // stable machine name used in answers + GHL fields
            $t->string('question');
            $t->string('type')->default('tiles'); // tiles | select
            $t->string('icon')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('quiz_options', function (Blueprint $t) {
            $t->id();
            $t->foreignId('quiz_step_id')->constrained()->cascadeOnDelete();
            $t->string('label');
            $t->string('value');
            $t->string('icon')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('enquiries', function (Blueprint $t) {
            $t->id();
            $t->string('form_name');              // hero-quiz | modal | package-quote | question | contact
            $t->string('first_name');
            $t->string('last_name');
            $t->string('email');
            $t->string('country_code', 8);
            $t->string('phone_national', 32);
            $t->string('phone_e164', 32);
            $t->string('travel_date')->nullable();
            $t->string('second_quote')->nullable();
            $t->text('message')->nullable();
            $t->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $t->string('package_name')->nullable();
            $t->string('tour_type')->nullable();
            $t->json('answers')->nullable();

            // consent / chargeback evidence
            $t->boolean('consent_given');
            $t->text('consent_text');
            $t->string('terms_version', 32);
            $t->timestamp('consent_at');
            $t->string('ip_address', 64)->nullable();
            $t->text('user_agent')->nullable();
            $t->text('page_url')->nullable();

            // tracking: first touch + latest touch
            $t->json('tracking_first')->nullable();
            $t->json('tracking_last')->nullable();

            // workflow
            $t->string('status')->default('new'); // new|contacted|quoted|won|lost
            $t->text('notes')->nullable();

            // GHL
            $t->string('ghl_status')->default('skipped'); // skipped|pending|synced|failed
            $t->string('ghl_contact_id')->nullable();
            $t->text('ghl_error')->nullable();
            $t->timestamp('ghl_synced_at')->nullable();

            $t->timestamps();
            $t->index(['status', 'created_at']);
            $t->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiries');
        Schema::dropIfExists('quiz_options');
        Schema::dropIfExists('quiz_steps');
    }
};
