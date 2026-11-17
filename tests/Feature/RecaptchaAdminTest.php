<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Setting;
use App\Services\Recaptcha;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RecaptchaAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['gotour.recaptcha.site_key' => null, 'gotour.recaptcha.secret_key' => null]);
    }

    private function admin(): void
    {
        $this->actingAs(AdminUser::where('email', 'admin@test.example')->firstOrFail());
    }

    private function enquiry(array $extra = []): array
    {
        return $extra + [
            'first_name' => 'Ada', 'last_name' => 'Lovelace', 'email' => 'ada@example.com', 'country' => 'US', 'phone' => '2025550123',
            'consent' => '1', 'form_name' => 'contact',
        ];
    }

    public function test_screen_loads_and_requires_login(): void
    {
        $this->get('/admin/recaptcha')->assertRedirect(route('admin.login'));
        $this->admin();
        $this->get('/admin/recaptcha')->assertOk()->assertSee('reCAPTCHA keys')->assertSee('Keys missing');
        $this->get('/admin')->assertSee('reCAPTCHA');
    }

    public function test_saving_encrypts_the_secret_and_the_keys_are_used(): void
    {
        $this->admin();
        $this->put('/admin/recaptcha', ['site_key' => 'SITEKEY123', 'secret_key' => 'SECRETKEY-CzzQ', 'enabled' => '1'])->assertRedirect();

        $this->assertStringStartsWith('enc:', Setting::get('recaptcha_secret_key'));
        $this->assertStringNotContainsString('SECRETKEY', Setting::get('recaptcha_secret_key'));
        $this->assertSame('SECRETKEY-CzzQ', Recaptcha::secret());
        $this->assertSame('SITEKEY123', Recaptcha::siteKey());

        $page = $this->get('/admin/recaptcha')->assertOk()->assertSee('Live keys in use')->assertSee('CzzQ');
        $this->assertStringNotContainsString('SECRETKEY-CzzQ', $page->getContent(), 'the full secret is never shown again');
        $this->get('/')->assertSee('name="recaptcha-site-key" content="SITEKEY123"', false);
    }

    public function test_blank_secret_keeps_the_saved_one_and_remove_clears_it(): void
    {
        $this->admin();
        $this->put('/admin/recaptcha', ['site_key' => 'S1', 'secret_key' => 'sec-one-1234', 'enabled' => '1']);
        $this->put('/admin/recaptcha', ['site_key' => 'S2', 'secret_key' => '', 'enabled' => '1']);
        $this->assertSame('sec-one-1234', Recaptcha::secret());

        $this->put('/admin/recaptcha', ['site_key' => 'S2', 'secret_remove' => '1', 'enabled' => '1']);
        $this->assertNull(Recaptcha::secret());
    }

    public function test_a_plain_secret_saved_earlier_in_settings_still_works(): void
    {
        Setting::put('recaptcha_secret_key', 'legacy-plain-secret');

        $this->assertSame('legacy-plain-secret', Recaptcha::secret());
    }

    public function test_keys_are_validated(): void
    {
        $this->admin();
        $this->put('/admin/recaptcha', ['site_key' => 'bad key!', 'secret_key' => 'x'])->assertSessionHasErrors('site_key');
    }

    public function test_test_secret_key_distinguishes_a_valid_and_an_invalid_secret(): void
    {
        $this->admin();
        $this->put('/admin/recaptcha', ['site_key' => 'S', 'secret_key' => 'good-secret', 'enabled' => '1']);

        Http::fake(['www.google.com/recaptcha/*' => Http::sequence()
            ->push(['success' => false, 'error-codes' => ['invalid-input-response']])
            ->push(['success' => false, 'error-codes' => ['invalid-input-secret']])]);
        $this->post('/admin/recaptcha/test')->assertSessionHas('status', fn ($s) => str_contains($s, 'Google accepts'));
        $this->post('/admin/recaptcha/test')->assertSessionHasErrors('recaptcha');
    }

    public function test_switching_it_off_removes_the_widget_and_the_server_stops_requiring_it(): void
    {
        $this->admin();
        $this->put('/admin/recaptcha', ['site_key' => 'SITEKEY123', 'secret_key' => 'sec-1234', 'enabled' => '1']);
        $this->get('/contact')->assertSee('x-ref="captcha"', false);

        $this->put('/admin/recaptcha', ['site_key' => 'SITEKEY123', 'secret_key' => '', 'enabled' => '0']);
        $this->assertFalse(Recaptcha::enabled());

        $this->get('/contact')->assertOk()->assertDontSee('x-ref="captcha"', false)->assertDontSee('recaptcha-site-key', false)->assertDontSee('reCAPTCHA keys are not set', false);
        $this->get('/admin/recaptcha')->assertSee('Off: forms are not protected');

        Http::fake();
        $this->postJson('/enquiry', $this->enquiry())->assertSuccessful();
        $this->assertDatabaseHas('enquiries', ['email' => 'ada@example.com']);
        Http::assertNothingSent();
    }

    public function test_when_it_is_on_a_missing_token_is_still_rejected(): void
    {
        $this->admin();
        $this->put('/admin/recaptcha', ['site_key' => 'S', 'secret_key' => 'sec-1234', 'enabled' => '1']);

        $this->postJson('/enquiry', $this->enquiry())->assertStatus(422)->assertJsonValidationErrors('g-recaptcha-response');
    }
}
