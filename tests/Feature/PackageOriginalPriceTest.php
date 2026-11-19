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
        $this->assertTrue(strpos($html, 'Was from '.self::EUR.'1,800') < strpos($html, self::EUR.'1,500</p>'), 'the crossed-out line comes before (above) the current price');
        $this->assertStringContainsString('Was from '.self::EUR.'1,800', $html);
    }

    public function test_no_strikethrough_without_an_original_price_or_when_it_is_not_higher(): void
    {
        $this->package(['price_from' => 1500, 'original_price' => null]);
        $html = $this->get('/packages')->assertOk()->getContent();
        $this->assertStringNotContainsString('<s class=', $html);
        $this->assertStringContainsString(self::EUR.'1,500', $html);

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
}
