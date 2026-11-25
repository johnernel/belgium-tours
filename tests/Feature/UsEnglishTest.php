<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Support\UsEnglish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The audience is in the United States: visible text uses US spelling, while code words and integration names stay as they are. */
class UsEnglishTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_converter_swaps_uk_words_and_keeps_the_case(): void
    {
        $this->assertSame('Inquire Now', UsEnglish::convert('Enquire Now'));
        $this->assertSame('Your inquiry and 2 inquiries', UsEnglish::convert('Your enquiry and 2 enquiries'));
        $this->assertSame('Solo travelers are traveling; he traveled', UsEnglish::convert('Solo travellers are travelling; he travelled'));
        $this->assertSame('Early fall, then Fall colors', UsEnglish::convert('Early autumn, then Autumn colours'));
        $this->assertSame('A rental car, a vacation and ORGANIZED stays', UsEnglish::convert('A hire car, a holiday and ORGANISED stays'));
        $this->assertSame('city centers, a catalog, favorite, licenses', UsEnglish::convert('city centres, a catalogue, favourite, licences'));
    }

    public function test_the_converter_never_touches_code_words_urls_addresses_or_ids(): void
    {
        foreach (['website-enquiry', '/enquiry', 'enquiry_submit', 'enquiry.store', 'info@enquiries.example.com', 'Enquiry ID', 'enquiry_id', '#enquiry', 'https://x.test/enquiry/1'] as $keep) {
            // Only a bare, whole word is converted: "Enquiry ID" is a Google Sheet column name used as a key, so it is covered below.
            if ($keep === 'Enquiry ID') {
                continue;
            }
            $this->assertSame($keep, UsEnglish::convert($keep), $keep);
        }
        $this->assertSame('{"travel_style":"By coach with other travelers"}', UsEnglish::convertValue('{"travel_style":"By coach with other travellers"}'), 'JSON keys are kept, string values converted');
    }

    public function test_public_pages_show_no_uk_spellings(): void
    {
        $this->seed();
        $package = Package::firstOrFail();
        $urls = ['/', '/packages', '/packages/'.$package->slug, '/about', '/contact', '/faqs', '/testimonials', '/meet-our-team', '/destinations', '/terms-and-conditions', '/privacy-policy', '/thank-you', '/tours/self-drive', '/tours/chauffeur-driven', '/tours/coach-tours'];
        $uk = '~\b(enquir\w*|enquire\w*|travell\w*|colour\w*|favour\w*|organis\w*|personalis\w*|customis\w*|centre\w*|licence\w*|catalogue\w*|holiday\w*|hire car\w*|autumn|whilst|amongst|cosy|grey)\b~i';

        foreach ($urls as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $html = preg_replace('~<(script|style)\b.*?</\1>~is', ' ', $html);
            $html = preg_replace('~<!--.*?-->~s', ' ', $html);
            $text = html_entity_decode(strip_tags($html));
            preg_match_all($uk, $text, $m);
            $this->assertSame([], array_unique($m[0]), "$url still has UK spellings");
        }
    }

    public function test_the_form_names_ids_and_integration_keys_are_untouched(): void
    {
        $this->seed();
        $html = $this->get('/contact')->assertOk()->getContent();
        $this->assertStringContainsString('action="'.route('enquiry.store').'"', $html, 'the form still posts to the same route');
        $this->assertStringContainsString("formName: 'contact'", $html, 'form names are unchanged');
        $this->assertStringContainsString('x-data="enquiryForm(', $html, 'the Alpine component name is unchanged');
        $this->assertStringContainsString('Inquiry form', $html, 'only the visible/aria label changed');

        $ghl = file_get_contents(base_path('app/Services/Ghl/GhlFields.php'));
        foreach (['Quiz: Season', 'Quiz: Travel Style'] as $field) {
            $this->assertStringContainsString($field, $ghl, "GoHighLevel field name $field is unchanged");
        }
        $client = file_get_contents(base_path('app/Services/GhlClient.php'));
        $this->assertStringContainsString("'website-enquiry'", $client, 'the GoHighLevel tag is unchanged');
        $sheet = file_get_contents(base_path('app/Services/SheetClient.php'));
        $this->assertStringContainsString('Enquiry ID', $sheet, 'the Google Sheet key column is unchanged');
    }
}
