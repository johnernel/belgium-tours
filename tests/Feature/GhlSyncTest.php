<?php

namespace Tests\Feature;

use App\Jobs\SyncEnquiryToGhl;
use App\Models\Enquiry;
use App\Services\Ghl\GhlFields;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** All HTTP is faked: these tests never reach the live CRM. */
class GhlSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Cache::flush();
    }

    private function enableGhl(): void
    {
        config(['gotour.ghl.token' => 'pit-secret-token', 'gotour.ghl.location_id' => 'LOC123']);
    }

    private function enquiry(): Enquiry
    {
        return Enquiry::create([
            'form_name' => 'contact', 'first_name' => 'Ann', 'last_name' => 'Lee', 'email' => 'ann@example.com', 'country_code' => 'US',
            'phone_national' => '2025550123', 'phone_e164' => '+12025550123', 'consent_given' => true, 'consent_text' => 'x',
            'terms_version' => 'v1', 'consent_at' => now(), 'ghl_status' => 'pending',
            'tracking_first' => ['utm_source' => 'google'], 'tracking_last' => ['utm_source' => 'google'],
        ]);
    }

    public function test_setup_creates_only_missing_fields_and_is_safe_to_rerun(): void
    {
        $this->enableGhl();
        $existing = collect(GhlFields::definitions())->keys()->take(3)->map(fn ($n, $i) => ['id' => "ex$i", 'name' => $n])->all();
        Http::fake([
            '*/customFields?model=contact' => Http::response(['customFields' => $existing]),
            '*/customFields' => Http::response(['customField' => ['id' => 'new1']]),
        ]);

        $this->artisan('ghl:setup')->assertSuccessful();

        $created = Http::recorded(fn (Request $r) => $r->method() === 'POST')->count();
        $this->assertSame(count(GhlFields::definitions()) - 3, $created);
        Http::assertSent(fn (Request $r) => $r->hasHeader('Authorization', 'Bearer pit-secret-token') && $r->hasHeader('Version'));
    }

    public function test_setup_does_nothing_when_ghl_is_off(): void
    {
        Http::fake();

        $this->artisan('ghl:setup')->assertFailed();
        Http::assertNothingSent();
    }

    public function test_job_upserts_contact_and_records_the_contact_id(): void
    {
        $this->enableGhl();
        $ids = collect(GhlFields::definitions())->keys()->map(fn ($n) => ['id' => 'id-'.md5($n), 'name' => $n])->all();
        Http::fake([
            '*/customFields*' => Http::response(['customFields' => $ids]),
            '*/contacts/search/duplicate*' => Http::response([], 404),
            '*/contacts/upsert' => Http::response(['contact' => ['id' => 'GHL-1'], 'new' => true]),
        ]);
        $e = $this->enquiry();

        (new SyncEnquiryToGhl($e->id))->handle(app(\App\Services\GhlClient::class));

        $e->refresh();
        $this->assertSame('synced', $e->ghl_status);
        $this->assertSame('GHL-1', $e->ghl_contact_id);
        Http::assertSent(function (Request $r) {
            return str_ends_with($r->url(), '/contacts/upsert')
                && $r['email'] === 'ann@example.com' && $r['phone'] === '+12025550123' && $r['locationId'] === 'LOC123'
                && collect($r['customFields'])->pluck('field_value')->contains('google');
        });
    }

    public function test_failure_is_recorded_without_leaking_the_token(): void
    {
        $this->enableGhl();
        Http::fake(['*' => Http::response(['message' => 'nope'], 401)]);
        $e = $this->enquiry();

        try {
            (new SyncEnquiryToGhl($e->id))->handle(app(\App\Services\GhlClient::class));
            $this->fail('expected the job to throw so the queue retries it');
        } catch (\Throwable $ex) {
            // expected
        }

        $e->refresh();
        $this->assertSame('failed', $e->ghl_status);
        $this->assertStringNotContainsString('pit-secret-token', (string) $e->ghl_error);
    }

    public function test_a_crm_outage_never_breaks_the_visitors_enquiry(): void
    {
        $this->enableGhl();
        Http::fake([
            'www.google.com/*' => Http::response(['success' => true]),
            '*' => Http::response('boom', 500),
        ]);

        $this->postJson('/enquiry', [
            'form_name' => 'contact', 'first_name' => 'Ann', 'last_name' => 'Lee', 'email' => 'ann@example.com', 'country' => 'US',
            'phone' => '2025550123', 'consent' => '1', 'g-recaptcha-response' => 'x',
        ])->assertOk()->assertJsonStructure(['redirect']);

        $this->assertSame('failed', Enquiry::firstOrFail()->ghl_status);
    }

    public function test_admin_can_retry_a_failed_sync(): void
    {
        $this->enableGhl();
        $ids = collect(GhlFields::definitions())->keys()->map(fn ($n) => ['id' => 'id-'.md5($n), 'name' => $n])->all();
        Http::fake([
            '*/customFields*' => Http::response(['customFields' => $ids]),
            '*/contacts/search/duplicate*' => Http::response([], 404),
            '*/contacts/upsert' => Http::response(['contact' => ['id' => 'GHL-9']]),
        ]);
        $e = $this->enquiry();
        $e->update(['ghl_status' => 'failed']);

        $this->actingAs(\App\Models\AdminUser::firstOrFail())->post("/admin/enquiries/{$e->id}/ghl-retry")->assertRedirect();

        $this->assertSame('synced', $e->fresh()->ghl_status);
    }
}
