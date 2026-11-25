<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Enquiry;
use App\Models\Setting;
use App\Services\DashboardStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The admin Dashboard shows live numbers computed from the saved enquiries and the site settings, never placeholders. */
class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->actingAs(AdminUser::firstOrFail());
    }

    private function enquiry(array $o = []): Enquiry
    {
        return Enquiry::create($o + [
            'form_name' => 'hero-quiz', 'first_name' => 'Ann', 'last_name' => 'Lee', 'email' => 'ann@example.com', 'country_code' => 'US',
            'phone_national' => '2125551234', 'phone_e164' => '+12125551234', 'consent_given' => true, 'consent_text' => 'x', 'terms_version' => 'v1',
            'consent_at' => now(), 'status' => 'new',
        ]);
    }

    public function test_an_empty_database_shows_zeros_and_nothing_yet(): void
    {
        $this->get('/admin')->assertOk()->assertSee('Enquiries, last 30 days')->assertSee('Nothing yet.')->assertSee('No enquiries yet.')->assertDontSee('Needs your attention');
    }

    public function test_the_widgets_count_the_real_enquiries(): void
    {
        $old = $this->enquiry(['first_name' => 'Old', 'created_at' => now()->subDays(3), 'tracking_last' => ['gclid' => 'abc', 'utm_campaign' => 'spring-search', 'utm_source' => 'google', 'utm_medium' => 'cpc']]);
        $this->enquiry(['first_name' => 'Bea', 'form_name' => 'modal', 'package_name' => 'Bruges Weekend', 'status' => 'won', 'tracking_last' => ['referrer' => 'https://www.chatgpt.com/x']]);
        $this->enquiry(['first_name' => 'Cy', 'form_name' => 'contact', 'status' => 'quoted']);
        $this->enquiry(['first_name' => 'Ancient', 'created_at' => now()->subDays(60)]);

        $stats = new DashboardStats(30);
        $s = $stats->summary();
        $this->assertSame(3, $s['total'], 'the 60 day old enquiry is outside 30 days');
        $this->assertSame(1, $s['ads']);
        $this->assertSame('33%', $s['adsPct']);
        $this->assertSame(1, $s['won']);
        $this->assertSame(2, $s['newAll'], 'Old and Ancient are still new');
        $this->assertEquals(['Main quiz' => 1, 'Quote popup' => 1, 'Contact form' => 1], $stats->forms());
        $this->assertSame(['Bruges Weekend' => 1], $stats->journeys());
        $this->assertSame(['spring-search' => 1], $stats->campaigns());
        $this->assertArrayHasKey('google / cpc', $stats->sources());
        $this->assertArrayHasKey('chatgpt.com', $stats->sources());
        $this->assertSame(2, $stats->waiting(), 'Old (3 days) and Ancient (60 days) are still new after more than a day');
    }

    public function test_the_page_renders_the_numbers_and_the_period_filter_changes_them(): void
    {
        $this->enquiry(['created_at' => now()->subDays(20), 'first_name' => 'Mid']);
        $this->enquiry(['first_name' => 'Fresh']);

        $this->get('/admin?days=7')->assertOk()->assertSee('Enquiries, last 7 days')->assertSee('Fresh')->assertSee('Open the inbox');
        $this->assertSame(1, (new DashboardStats(7))->summary()['total']);
        $this->assertSame(2, (new DashboardStats(30))->summary()['total']);
        $this->assertSame(30, DashboardStats::period('999'), 'an unknown period falls back to 30 days');
        $this->get('/admin?days=90')->assertOk()->assertSee('Enquiries, last 90 days');
    }

    public function test_attention_banner_and_site_health_reflect_the_real_state(): void
    {
        $this->enquiry(['created_at' => now()->subDays(2)]);
        $this->enquiry(['ghl_status' => 'failed']);

        $this->get('/admin')->assertOk()->assertSee('Needs your attention')->assertSee('waited more than 24 hours')->assertSee('failed to sync to GoHighLevel');

        Setting::put('phone', '+1 212 555 1234');
        Setting::put('gtm_id', 'GTM-ABC123');
        Setting::put('notification_emails', 'team@example.com');
        $health = collect((new DashboardStats(30))->health())->mapWithKeys(fn ($h) => [$h[1] => $h[0]]);
        $this->assertTrue($health['Phone number set']);
        $this->assertTrue($health['Google Tag Manager ID set']);
        $this->assertTrue($health['Notification email set']);
        $this->assertFalse($health['GoHighLevel connected'], 'no token in the test environment');
        // Google's public test keys do not count as real keys; other keys do\n        Setting::put('recaptcha_site_key', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI');\n        $this->assertFalse(collect((new DashboardStats(30))->health())->mapWithKeys(fn ($h) => [$h[1] => $h[0]])['Real reCAPTCHA keys']);\n        Setting::put('recaptcha_site_key', 'real-looking-site-key');\n        $this->assertTrue(collect((new DashboardStats(30))->health())->mapWithKeys(fn ($h) => [$h[1] => $h[0]])['Real reCAPTCHA keys']);
    }
}
