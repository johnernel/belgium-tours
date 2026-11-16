<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Enquiry;
use App\Models\Package;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function admin(): AdminUser
    {
        return AdminUser::where('email', 'admin@test.example')->firstOrFail();
    }

    public function test_admin_pages_require_login(): void
    {
        foreach (['/admin', '/admin/enquiries', '/admin/packages', '/admin/settings', '/admin/quiz', '/admin/users', '/admin/media'] as $url) {
            $this->get($url)->assertRedirect(route('admin.login'));
        }
    }

    public function test_admin_post_actions_require_login(): void
    {
        $this->post('/admin/packages/reorder')->assertRedirect(route('admin.login'));
        $this->put('/admin/settings')->assertRedirect(route('admin.login'));
    }

    public function test_login_works_and_wrong_password_fails(): void
    {
        $this->post('/admin/login', ['email' => 'admin@test.example', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/admin/login', ['email' => 'admin@test.example', 'password' => 'secret-pass-123'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin());
    }

    public function test_login_is_throttled(): void
    {
        $last = null;
        for ($i = 0; $i < 7; $i++) {
            $last = $this->post('/admin/login', ['email' => 'admin@test.example', 'password' => 'bad'.$i])->status();
        }

        $this->assertSame(429, $last);
    }

    public function test_passwords_are_hashed(): void
    {
        $this->assertNotSame('secret-pass-123', $this->admin()->password);
        $this->assertTrue(\Hash::check('secret-pass-123', $this->admin()->password));
    }

    public function test_every_admin_screen_loads_for_a_logged_in_admin(): void
    {
        $package = Package::firstOrFail();
        $this->actingAs($this->admin());

        foreach ([
            '/admin', '/admin/enquiries', '/admin/packages', '/admin/packages/create', "/admin/packages/{$package->id}/edit",
            '/admin/tour-types', '/admin/tour-types/create', '/admin/destinations', '/admin/quiz', '/admin/pages', '/admin/pages/1/edit',
            '/admin/testimonials', '/admin/faqs', '/admin/media', '/admin/settings', '/admin/users', '/admin/users/create',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_crud_edit_screens_load(): void
    {
        $this->actingAs($this->admin());
        $type = \App\Models\TourType::firstOrFail();

        $this->get("/admin/tour-types/{$type->id}/edit")->assertOk()->assertSee($type->name);
    }

    public function test_package_can_be_created_published_and_duplicated(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/packages', [
            'title' => 'Test Trip', 'nights' => 5, 'currency' => 'EUR', 'price_from' => '1200',
            'is_published' => '1', 'highlights' => "One\nTwo", 'description' => '<p>Hi<script>alert(1)</script></p>',
            'itinerary' => [['title' => 'Day 1', 'text' => 'Arrive'], ['title' => '', 'text' => '']],
        ])->assertRedirect();

        $p = Package::where('slug', 'test-trip')->firstOrFail();
        $this->assertSame(['One', 'Two'], $p->highlights);
        $this->assertCount(1, $p->itinerary);
        $this->assertStringNotContainsString('<script', $p->description);
        $this->get('/packages/test-trip')->assertOk()->assertSee('From €1,200');

        $this->post("/admin/packages/{$p->id}/duplicate")->assertRedirect();
        $copy = Package::where('slug', 'test-trip-copy')->firstOrFail();
        $this->assertFalse($copy->is_published);
    }

    public function test_unpublished_package_is_hidden_from_the_public(): void
    {
        $p = Package::firstOrFail();
        $p->update(['is_published' => false]);

        $this->get('/packages/'.$p->slug)->assertNotFound();
    }

    public function test_new_hero_photo_requires_alt_text(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/packages', [
            'title' => 'Photo Trip', 'nights' => 3, 'currency' => 'EUR',
            'hero_image' => UploadedFile::fake()->image('hero.jpg', 1600, 900),
        ])->assertSessionHasErrors('hero_image_alt');
    }

    public function test_image_upload_is_resized_to_webp(): void
    {
        \Storage::fake('public');
        $this->actingAs($this->admin());

        $this->post('/admin/media', ['alt' => 'A test photo', 'files' => [UploadedFile::fake()->image('a.jpg', 2400, 1600)]])->assertSessionHasNoErrors();

        $m = \App\Models\Media::firstOrFail();
        foreach ([480, 800, 1280, 1600] as $w) {
            \Storage::disk('public')->assertExists('uploads/'.basename($m->path)."-$w.webp");
        }
    }

    public function test_non_images_are_rejected_by_media_upload(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/media', ['alt' => 'x', 'files' => [UploadedFile::fake()->create('evil.php', 10, 'application/x-php')]])->assertSessionHasErrors('files.0');
        $this->assertDatabaseCount('media', 0);
    }

    public function test_cannot_delete_yourself_or_the_last_admin(): void
    {
        $me = $this->admin();
        $this->actingAs($me);

        $this->delete("/admin/users/{$me->id}")->assertSessionHasErrors('user');
        $this->assertDatabaseHas('admin_users', ['id' => $me->id]);

        $other = AdminUser::create(['name' => 'Other', 'email' => 'other@example.com', 'password' => 'password-1234']);
        $this->delete("/admin/users/{$other->id}")->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('admin_users', ['id' => $other->id]);
    }

    public function test_enquiry_inbox_filter_status_notes_and_csv_export(): void
    {
        $this->actingAs($this->admin());
        $e = Enquiry::create([
            'form_name' => 'contact', 'first_name' => 'Zed', 'last_name' => '=cmd', 'email' => 'z@example.com', 'country_code' => 'US',
            'phone_national' => '2025550123', 'phone_e164' => '+12025550123', 'consent_given' => true, 'consent_text' => 'x',
            'terms_version' => 'v1', 'consent_at' => now(), 'tracking_last' => ['utm_source' => 'google'],
        ]);

        $this->get('/admin/enquiries?q=Zed&source=google')->assertOk()->assertSee('Zed');
        $this->get('/admin/enquiries?status=won')->assertOk()->assertDontSee('Zed');

        $this->put("/admin/enquiries/{$e->id}", ['status' => 'quoted', 'notes' => 'Called back'])->assertRedirect();
        $this->assertSame('quoted', $e->fresh()->status);

        $csv = $this->get('/admin/enquiries/export')->assertOk()->streamedContent();
        $this->assertStringContainsString('Zed', $csv);
        $this->assertStringContainsString("'=cmd", $csv, 'formula injection must be neutralised');

        $this->get("/admin/enquiries/{$e->id}")->assertOk()->assertSee('Consent evidence');
    }

    public function test_settings_save_and_validate(): void
    {
        $this->actingAs($this->admin());

        $this->put('/admin/settings', ['brand_name' => 'X', 'legal_company_name' => 'X Ltd', 'gtm_id' => 'bad'])->assertSessionHasErrors('gtm_id');

        $this->put('/admin/settings', [
            'brand_name' => 'Go Belgium Tours', 'legal_company_name' => 'Go Belgium Tours Ltd', 'phone' => '+32 2 000 0000',
            'notification_emails' => 'a@example.com, b@example.com',
        ])->assertSessionHasNoErrors();

        $this->assertSame('Go Belgium Tours Ltd', Setting::get('legal_company_name'));
        $this->get('/contact')->assertSee('Go Belgium Tours Ltd', false)->assertSee('tel:+3220000000', false);
    }

    public function test_quiz_edits_change_the_quiz_component(): void
    {
        $this->actingAs($this->admin());
        $step = \App\Models\QuizStep::where('key', 'season')->firstOrFail();

        $this->put("/admin/quiz/steps/{$step->id}", ['question' => 'When would you like to go?', 'type' => 'tiles', 'sort' => 2, 'active' => '1'])->assertRedirect();
        $this->assertStringContainsString('When would you like to go?', view('partials.quiz', ['quizSteps' => \App\Models\QuizStep::forQuiz()])->render());

        $this->put("/admin/quiz/steps/{$step->id}", ['question' => 'Hidden?', 'type' => 'tiles', 'sort' => 2])->assertRedirect(); // active unchecked => off
        $this->assertStringNotContainsString('Hidden?', view('partials.quiz', ['quizSteps' => \App\Models\QuizStep::forQuiz()])->render());
    }
}
