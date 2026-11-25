<?php

namespace Tests\Feature;

use App\Http\Requests\EnquiryRequest;
use App\Models\Enquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** The phone field: international number in, digits counted per country, field names untouched. */
class PhoneFieldTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $over = []): array
    {
        return $over + [
            'form_name' => 'contact', 'first_name' => 'Ann', 'last_name' => 'Lee', 'email' => 'ann@example.com', 'country' => 'US',
            'phone' => '+12125551234', 'consent' => '1', 'g-recaptcha-response' => 'token',
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(['www.google.com/recaptcha/*' => Http::response(['success' => true])]);
    }

    public function test_the_full_international_number_is_accepted_and_stored_without_the_dial_code_twice(): void
    {
        $this->postJson('/enquiry', $this->payload())->assertOk();
        $e = Enquiry::firstOrFail();
        $this->assertSame('2125551234', $e->phone_national);
        $this->assertSame('+12125551234', $e->phone_e164, 'the number GoHighLevel receives');
    }

    public function test_a_spaced_number_and_a_national_only_number_end_up_the_same(): void
    {
        $this->postJson('/enquiry', $this->payload(['phone' => '+1 (212) 555-1234']))->assertOk();
        $this->postJson('/enquiry', $this->payload(['phone' => '2125551234']))->assertOk();
        $this->assertSame(['+12125551234', '+12125551234'], Enquiry::pluck('phone_e164')->all());
    }

    public function test_us_numbers_need_exactly_ten_digits(): void
    {
        foreach (['+1212555123' => '9 digits', '+121255512345' => '11 digits', '' => 'missing'] as $phone => $why) {
            $this->postJson('/enquiry', $this->payload(['phone' => (string) $phone]))
                ->assertStatus(422)
                ->assertJsonPath('errors.phone.0', 'Please enter a valid 10-digit phone number.');
        }
        $this->assertSame(0, Enquiry::count());
    }

    public function test_other_countries_use_their_own_length_and_drop_the_trunk_zero(): void
    {
        $this->postJson('/enquiry', $this->payload(['country' => 'GB', 'phone' => '+44 07700 900123']))->assertOk();
        $this->assertSame('+447700900123', Enquiry::firstOrFail()->phone_e164);

        $this->postJson('/enquiry', $this->payload(['country' => 'GB', 'phone' => '+44 770090012']))->assertStatus(422)->assertJsonPath('errors.phone.0', 'Please enter a valid 10-digit phone number.');
        $this->postJson('/enquiry', $this->payload(['country' => 'IE', 'phone' => '+353 12345']))->assertStatus(422)->assertJsonPath('errors.phone.0', 'Please enter a valid phone number (8 to 9 digits).');
    }

    public function test_letters_and_symbols_are_stripped_on_the_server_too(): void
    {
        $this->postJson('/enquiry', $this->payload(['phone' => '+1 212-abc-555-1234!!']))->assertOk();
        $this->assertSame('+12125551234', Enquiry::firstOrFail()->phone_e164);
        $this->assertSame('2125551234', EnquiryRequest::nationalDigits('+1 (212) 555-1234', 'US'));
    }

    public function test_the_form_keeps_its_names_and_ids_and_gets_the_phone_helpers(): void
    {
        $html = $this->get('/contact')->assertOk()->getContent();

        $this->assertStringContainsString('name="phone"', $html, 'field name unchanged');
        $this->assertStringContainsString('id="c-phone"', $html, 'field id unchanged');
        $this->assertStringContainsString('name="country"', $html);
        $this->assertStringContainsString('inputmode="tel"', $html, 'numeric keypad on phones');
        $this->assertStringContainsString('x-model="country"', $html);
        $this->assertStringContainsString('\u0022US\u0022:{\u0022dial\u0022:\u00221\u0022,\u0022min\u0022:10,\u0022max\u0022:10}', $html, 'digit counts reach the browser');
        // the modal (every page) and the quiz use the same component
        $this->assertStringContainsString('id="m-phone"', $html);
        $this->assertStringContainsString('id="q-phone"', $this->get('/')->getContent());
    }
}
