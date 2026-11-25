<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageOriginalPriceTest extends TestCase
{
    use RefreshDatabase;

    private const EUR = "\u{20AC}";

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function package(array $attrs = []): Package
    {
        $p = Package::where('is_published', true)->firstOrFail();
        $p->update($attrs + ['currency' => 'EUR']);

        return $p->fresh();
    }

    public function test_a_reduced_package_shows_the_current_price_with_from_and_the_old_price_crossed_out(): void
    {
        $p = $this->package(['price_from' => 1500, 'original_price' => 1800]);

        $html = $this->get('/packages')->assertOk()->getContent();

        $this->assertStringContainsString('<s class=', $html);
        $this->assertMatchesRegularExpression('~<s [^>]*>From '.preg_quote(self::EUR, '~').'1,800</s>~u', $html, 'the word From and the old price are struck together');
        $this->assertStringContainsString(self::EUR.'1,500', $html);
        $this->assertTrue(strpos($html, 'Was from '.self::EUR.'1,800') < strpos($html, self::EUR.'1,500</span>'), 'the crossed-out price comes before the current price (same line)');
        $this->assertStringContainsString('Was from '.self::EUR.'1,800', $html);
        $this->assertStringNotContainsString('class="pkg-card__from"', $html, 'no plain From label above the current price on a reduced card');
    }

    public function test_no_strikethrough_without_an_original_price_or_when_it_is_not_higher(): void
    {
        $this->package(['price_from' => 1500, 'original_price' => null]);
        $html = $this->get('/packages')->assertOk()->getContent();
        $this->assertStringNotContainsString('<s class=', $html);
        $this->assertStringContainsString(self::EUR.'1,500', $html);
        $this->assertStringContainsString('<span class="pkg-card__from">From</span>', $html, 'a normal card keeps its From label');

        $this->package(['price_from' => 1500, 'original_price' => 1200]);
        $this->assertStringNotContainsString('<s class=', $this->get('/packages')->getContent());
    }

    public function test_price_on_request_cards_are_unchanged(): void
    {
        $this->package(['price_from' => null, 'original_price' => 999]);
        $html = $this->get('/packages')->assertOk()->getContent();
        $this->assertStringContainsString('Price on request', $html);
        $this->assertStringNotContainsString('<s class=', $html);
    }

    public function test_admin_saves_the_original_price_validates_it_and_ignores_it_without_a_current_price(): void
    {
        $this->actingAs(AdminUser::where('email', 'admin@test.example')->firstOrFail());
        $base = ['title' => 'Strike Trip', 'nights' => 3, 'currency' => 'EUR', 'is_published' => '1'];

        $this->post('/admin/packages', $base + ['price_from' => '1000', 'original_price' => '900'])->assertSessionHasErrors('original_price');

        $this->post('/admin/packages', $base + ['price_from' => '1000', 'original_price' => '1300'])->assertRedirect();
        $p = Package::where('slug', 'strike-trip')->firstOrFail();
        $this->assertSame('1300.00', (string) $p->original_price);

        $this->put("/admin/packages/{$p->id}", $base + ['price_from' => '', 'original_price' => '1300'])->assertRedirect();
        $this->assertNull($p->fresh()->original_price, 'no strikethrough price without a current price');

        $this->get("/admin/packages/{$p->id}/edit")->assertOk()->assertSee('Original price (optional)');
    }

    public function test_the_card_shows_the_package_summary_under_the_title_clamped_to_three_lines(): void
    {
        $p = $this->package(['summary' => 'A relaxed few days by hire car between two of Belgium best-preserved medieval cities.']);
        $html = $this->get('/packages')->assertOk()->getContent();
        $this->assertStringContainsString('<p class="pkg-card__desc">'.$p->summary.'</p>', $html);
        $this->assertLessThan(strpos($html, $p->summary), strpos($html, '>'.$p->title.'</h'), 'the summary comes after the title');

        $withSummary = substr_count($html, 'pkg-card__desc');
        $p->update(['summary' => '']);
        $this->assertSame($withSummary - 1, substr_count($this->get('/packages')->getContent(), 'pkg-card__desc'), 'a blank summary renders no description element');
    }

    public function test_every_card_has_the_badge_meta_row_more_info_link_and_quote_button_in_the_shared_layout(): void
    {
        $html = $this->get('/packages')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('~<span class="pkg-card__badge">Self Drive</span>~', $html, 'tour type badge on the photo');
        $this->assertMatchesRegularExpression('~<p class="pkg-card__meta">\s*<span>4 days</span>\s*<span>3 nights</span>\s*<span>Bruges · Ghent</span>~u', $html, 'days, nights and route in the meta row');
        $this->assertStringContainsString('class="pkg-card__more"', $html, 'More Info link');
        $this->assertStringContainsString('class="pkg-card__cta"', $html, 'Get a Quote button');
        $this->assertSame(substr_count($html, 'class="pkg-card group"'), substr_count($html, 'class="pkg-card__cta"'), 'one button per card');

        $css = file_get_contents(base_path('resources/css/app.css'));
        $this->assertStringContainsString('background-color: var(--color-card); /* #F7F4ED */', $css, 'card background #F7F4ED lives in the shared styles');
    }}
