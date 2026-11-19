<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Enquiry;
use App\Models\Setting;
use App\Services\SheetClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** All HTTP is faked: these tests never reach Google. */
class SheetSyncTest extends TestCase
{
    use RefreshDatabase;

    private const URL = 'https://script.google.com/macros/s/AKfycbxTESTid123/exec';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function connect(): void
    {
        Setting::put('sheet_webhook_url', Crypt::encryptString(self::URL));
        Setting::put('sheet_secret', Crypt::encryptString('secret-key-123456'));
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'form_name' => 'contact', 'first_name' => 'Ann', 'last_name' => 'Lee', 'email' => 'ann@example.com', 'country' => 'US',
            'phone' => '(202) 555-0123', 'consent' => '1', 'g-recaptcha-response' => 'token', 'page_url' => 'http://localhost/contact',
        ], $override);
    }

    public function test_every_form_submission_is_sent_to_the_sheet_with_its_utm_values(): void
    {
        $this->connect();
        Http::fake([
            'www.google.com/recaptcha/*' => Http::response(['success' => true]),
            'script.google.com/*' => Http::response(['ok' => true, 'row' => 2]),
        ]);

        $this->call('POST', '/enquiry', $this->payload(), [
            'gt_last' => json_encode(['utm_source' => 'google', 'utm_campaign' => 'belgium-brand', 'gclid' => 'GCL123']),
            'gt_first' => json_encode(['utm_source' => 'facebook']),
        ], [], ['HTTP_ACCEPT' => 'application/json'])->assertOk();

        Http::assertSent(function (Request $r) {
            if (! str_starts_with($r->url(), 'https://script.google.com/')) {
                return false;
            }
            $row = $r['row'];

            return $r['secret'] === 'secret-key-123456'
                && $r['headers'] === SheetClient::headers()
                && $row['Email'] === 'ann@example.com'
                && $row['UTM source (last)'] === 'google'
                && $row['UTM campaign (last)'] === 'belgium-brand'
                && $row['GCLID (last)'] === 'GCL123'
                && $row['UTM source (first)'] === 'facebook'
                && $row['Form'] === 'contact'
                && ! array_key_exists('IP', $row);
        });
        $this->assertSame('synced', Enquiry::firstOrFail()->sheet_status);
    }

    public function test_a_failing_sheet_never_fails_the_enquiry(): void
    {
        $this->connect();
        Http::fake([
            'www.google.com/recaptcha/*' => Http::response(['success' => true]),
            'script.google.com/*' => Http::response(['ok' => false, 'error' => 'Wrong secret key']),
        ]);

        $this->postJson('/enquiry', $this->payload())->assertOk();

        $e = Enquiry::firstOrFail();
        $this->assertSame('failed', $e->sheet_status);
        $this->assertStringContainsString('Wrong secret key', (string) $e->sheet_error);
    }

    public function test_nothing_is_sent_while_the_sheet_is_not_set_up(): void
    {
        Http::fake(['www.google.com/recaptcha/*' => Http::response(['success' => true]), 'script.google.com/*' => Http::response(['ok' => true])]);

        $this->postJson('/enquiry', $this->payload())->assertOk();

        Http::assertNotSent(fn (Request $r) => str_starts_with($r->url(), 'https://script.google.com/'));
        $this->assertSame('skipped', Enquiry::firstOrFail()->sheet_status);
    }

    public function test_admin_screen_saves_encrypted_values_and_validates_the_url(): void
    {
        $this->actingAs(AdminUser::where('email', 'admin@test.example')->firstOrFail());

        $this->get('/admin/sheets')->assertOk()->assertSee('Google Sheets')->assertSee('Off: add the web app URL');

        $this->put('/admin/sheets', ['webhook_url' => 'https://evil.example/exec', 'secret' => 'secret-key-123456'])->assertSessionHasErrors('webhook_url');

        $this->put('/admin/sheets', ['webhook_url' => self::URL, 'secret' => 'secret-key-123456', 'send' => '1'])->assertRedirect();
        $this->assertStringNotContainsString('AKfycbx', (string) Setting::get('sheet_webhook_url'), 'URL stored encrypted');
        $this->assertStringNotContainsString('secret-key', (string) Setting::get('sheet_secret'), 'secret stored encrypted');
        $this->assertTrue(app(SheetClient::class)->enabled());

        $page = $this->get('/admin/sheets')->assertOk()->assertSee('On: every enquiry')->assertSee('3456');
        $this->assertStringNotContainsString('secret-key-123456', $page->getContent());

        $this->put('/admin/sheets', ['webhook_url' => '', 'secret' => '']); // box left blank, switch unticked: keeps the connection but pauses
        $this->assertTrue(app(SheetClient::class)->paused());
        $this->assertSame(self::URL, app(SheetClient::class)->url());
    }

    public function test_test_row_and_backfill_work_and_report_errors(): void
    {
        $this->actingAs(AdminUser::where('email', 'admin@test.example')->firstOrFail());
        $this->connect();
        Http::fake(['script.google.com/*' => Http::response(['ok' => true])]);

        $this->post('/admin/sheets/test')->assertSessionHas('status');
        Http::assertSent(fn (Request $r) => ($r['row']['First name'] ?? null) === 'TEST' && ($r['row']['Form'] ?? null) === 'test');

        Enquiry::create(['form_name' => 'contact', 'first_name' => 'Old', 'last_name' => 'Lead', 'email' => 'old@example.com', 'country_code' => 'US', 'phone_national' => '2025550123', 'phone_e164' => '+12025550123', 'consent_given' => true, 'consent_text' => 'x', 'terms_version' => 'v1', 'consent_at' => now()]);
        $this->post('/admin/sheets/backfill')->assertSessionHas('status', fn ($s) => str_contains($s, 'Sent 1 past enquiry'));
        $this->assertSame('synced', Enquiry::where('email', 'old@example.com')->firstOrFail()->sheet_status);

        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(['script.google.com/*' => Http::response(['ok' => false, 'error' => 'Wrong secret key'])]);
        $this->post('/admin/sheets/test')->assertSessionHasErrors('sheet');
        $this->assertStringContainsString('Wrong secret key', session('errors')->first('sheet'));
    }

    public function test_the_sidebar_links_to_the_screen_and_it_requires_login(): void
    {
        $this->actingAs(AdminUser::where('email', 'admin@test.example')->firstOrFail());
        $this->get('/admin')->assertSee('Google Sheets');
        auth()->logout();
        $this->get('/admin/sheets')->assertRedirect(route('admin.login'));
    }
}
