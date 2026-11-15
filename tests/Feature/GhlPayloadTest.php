<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Services\Ghl\GhlFields;
use App\Services\GhlClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GhlPayloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Http::fake(['www.google.com/recaptcha/*' => Http::response(['success' => true])]);
    }

    public function test_ghl_is_off_when_keys_are_blank(): void
    {
        $this->assertFalse(app(GhlClient::class)->enabled());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('formTypes')]
    public function test_payload_carries_tracking_fields_for_every_form_type(string $form): void
    {
        $first = ['utm_source' => 'google', 'utm_medium' => 'cpc', 'utm_campaign' => 'belgium', 'utm_term' => 'belgium tours',
            'utm_content' => 'ad1', 'gclid' => 'G123', 'gbraid' => 'GB1', 'wbraid' => 'WB1',
            'referrer' => 'https://www.google.com/', 'landing_page' => 'http://localhost/?gclid=G123'];
        $last = array_merge($first, ['utm_source' => 'bing', 'gclid' => 'G999']);

        $this->call('POST', '/enquiry', [
            'form_name' => $form, 'first_name' => 'Ann', 'last_name' => 'Lee', 'email' => 'ann@example.com',
            'country' => 'US', 'phone' => '2025550123', 'consent' => '1', 'g-recaptcha-response' => 'x',
            'answers' => '{"destination":"belgium","budget":"2000"}',
        ], ['gt_first' => json_encode($first), 'gt_last' => json_encode($last)], [], ['HTTP_ACCEPT' => 'application/json'])->assertOk();

        $enquiry = Enquiry::firstOrFail();
        $ids = collect(GhlFields::definitions())->keys()->mapWithKeys(fn ($n) => [$n => 'id-'.md5($n)])->all();
        $payload = app(GhlClient::class)->payload($enquiry, $ids);

        $byName = [];
        foreach ($payload['customFields'] as $cf) {
            $byName[array_search($cf['id'], $ids)] = $cf['field_value'];
        }

        $this->assertSame('google', $byName['UTM Source (First)']);
        $this->assertSame('bing', $byName['UTM Source (Last)']);
        $this->assertSame('G123', $byName['GCLID (First)']);
        $this->assertSame('G999', $byName['GCLID (Last)']);
        foreach (['UTM Medium', 'UTM Campaign', 'UTM Term', 'UTM Content', 'GBRAID', 'WBRAID', 'Referrer', 'Landing Page'] as $label) {
            $this->assertArrayHasKey("$label (First)", $byName);
            $this->assertArrayHasKey("$label (Last)", $byName);
        }
        $this->assertSame($form, $byName['Form Name']);
        $this->assertSame('Yes', $byName['Consent Given']);
        $this->assertArrayHasKey('Consent Timestamp', $byName);
        $this->assertArrayHasKey('Consent Text Version', $byName);
        $this->assertArrayHasKey('Quiz Answers', $byName);
        $this->assertSame('+12025550123', $payload['phone']);
        $this->assertContains('form: '.$form, $payload['tags']);
    }

    public function test_returning_contact_keeps_existing_first_touch_values(): void
    {
        $e = new Enquiry([
            'form_name' => 'contact', 'first_name' => 'A', 'last_name' => 'B', 'email' => 'a@b.c', 'phone_e164' => '+1',
            'consent_given' => true, 'tracking_first' => ['utm_source' => 'new-first'], 'tracking_last' => ['utm_source' => 'new-last'],
        ]);
        $ids = collect(GhlFields::definitions())->keys()->mapWithKeys(fn ($n) => [$n => 'id-'.md5($n)])->all();

        $payload = app(GhlClient::class)->payload($e, $ids, ['UTM Source (First)' => 'original']);
        $values = collect($payload['customFields'])->pluck('field_value', 'id');

        $this->assertArrayNotHasKey($ids['UTM Source (First)'], $values->all(), 'first-touch must not be overwritten');
        $this->assertSame('new-last', $values[$ids['UTM Source (Last)']]);
    }

    public static function formTypes(): array
    {
        return [['hero-quiz'], ['modal'], ['package-quote'], ['question'], ['contact']];
    }
}
