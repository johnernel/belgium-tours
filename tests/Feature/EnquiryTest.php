<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Support\Consent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EnquiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->googleSays(true);
    }

    /** Replace the fake Google reply (Http::fake appends, so reset the factory first). */
    private function googleSays(bool $ok): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(['www.google.com/recaptcha/*' => Http::response(['success' => $ok])]);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'form_name' => 'contact',
            'first_name' => 'Ann',
            'last_name' => 'Lee',
            'email' => 'Ann@Example.com',
            'country' => 'US',
            'phone' => '(202) 555-0123',
            'consent' => '1',
            'g-recaptcha-response' => 'token',
            'page_url' => 'http://localhost/contact',
        ], $override);
    }

    public function test_enquiry_fails_without_consent(): void
    {
        $data = $this->payload();
        unset($data['consent']);

        $this->postJson('/enquiry', $data)->assertStatus(422)->assertJsonValidationErrors('consent');
        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_enquiry_fails_when_recaptcha_token_is_rejected(): void
    {
        $this->googleSays(false);

        $this->postJson('/enquiry', $this->payload())->assertStatus(422)->assertJsonValidationErrors('g-recaptcha-response');
        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_enquiry_fails_when_recaptcha_token_is_missing(): void
    {
        $data = $this->payload();
        unset($data['g-recaptcha-response']);

        $this->postJson('/enquiry', $data)->assertStatus(422)->assertJsonValidationErrors('g-recaptcha-response');
    }

    public function test_enquiry_fails_closed_when_google_is_unreachable(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('down'));

        $this->postJson('/enquiry', $this->payload())->assertStatus(422)->assertJsonValidationErrors('g-recaptcha-response');
        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_valid_enquiry_is_saved_with_consent_evidence(): void
    {
        $this->withHeaders(['User-Agent' => 'TestBrowser/1.0'])
            ->postJson('/enquiry', $this->payload(['message' => 'Hello <b>there</b>', 'answers' => '{"season":"summer","nights":"7"}']))
            ->assertOk()->assertJson(['redirect' => route('thank-you')]);

        $e = Enquiry::firstOrFail();
        $this->assertSame('ann@example.com', $e->email);
        $this->assertSame('+12025550123', $e->phone_e164);
        $this->assertTrue($e->consent_given);
        $this->assertSame(Consent::text(), $e->consent_text);
        $this->assertStringContainsString('Consent is not a condition of purchase.', $e->consent_text);
        $this->assertSame(config('gotour.terms_version'), $e->terms_version);
        $this->assertNotNull($e->consent_at);
        $this->assertNotEmpty($e->ip_address);
        $this->assertSame('TestBrowser/1.0', $e->user_agent);
        $this->assertSame('http://localhost/contact', $e->page_url);
        $this->assertSame('Hello there', $e->message);
        $this->assertSame(['season' => 'summer', 'nights' => '7'], $e->answers);
        $this->assertSame('skipped', $e->ghl_status, 'GHL must be off in tests');
    }

    public function test_phone_is_stored_as_e164_for_countries_with_a_trunk_zero(): void
    {
        $this->postJson('/enquiry', $this->payload(['country' => 'GB', 'phone' => '07700 900123']))->assertOk();

        $this->assertSame('+447700900123', Enquiry::firstOrFail()->phone_e164);
    }

    public function test_honeypot_submissions_are_silently_dropped(): void
    {
        $this->postJson('/enquiry', $this->payload(['website' => 'http://spam.example']))->assertOk();

        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_no_javascript_form_post_redirects_to_thank_you(): void
    {
        $this->post('/enquiry', $this->payload())->assertRedirect(route('thank-you'));
        $this->assertDatabaseCount('enquiries', 1);
    }

    public function test_package_enquiry_records_the_package(): void
    {
        $package = \App\Models\Package::firstOrFail();

        $this->postJson('/enquiry', $this->payload(['form_name' => 'package-quote', 'package_id' => $package->id]))->assertOk();

        $e = Enquiry::firstOrFail();
        $this->assertSame($package->id, $e->package_id);
        $this->assertSame($package->title, $e->package_name);
    }

    public function test_enquiry_endpoint_is_rate_limited(): void
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = $this->postJson('/enquiry', $this->payload(['email' => "u$i@example.com"]))->status();
        }

        $this->assertContains(429, $codes);
    }

    public function test_consent_checkbox_is_rendered_unticked_with_exact_wording(): void
    {
        $html = $this->get('/contact')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<input id="c-consent"[^>]*type="checkbox"(?![^>]*checked)[^>]*>/', $html);
        foreach ([
            'provided by Go Belgium Tours. By providing my phone number, I consent to receive SMS messages from Go Belgium Tours regarding my enquiry, booking updates, itinerary updates, reminders, and customer support. Message and data rates may apply. Reply STOP to unsubscribe or HELP for assistance. Consent is not a condition of purchase.',
            'target="_blank"',
        ] as $needle) {
            $this->assertStringContainsString($needle, $html);
        }
    }
}
