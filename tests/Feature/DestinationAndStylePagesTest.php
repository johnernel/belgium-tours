<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Experience;
use App\Models\Package;
use App\Models\TravelStyle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Destinations and Travel Styles: two header mega menus, and a page of its own for every destination and every travel style. */
class DestinationAndStylePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_the_header_menus_link_every_destination_and_every_travel_style(): void
    {
        $html = $this->get('/packages')->assertOk()->getContent();

        $this->assertStringContainsString('aria-controls="mega-destinations-test"', $html);
        $this->assertStringContainsString('aria-controls="mega-styles-test"', $html);
        $this->assertGreaterThanOrEqual(6, Experience::active()->count());
        foreach (Experience::active()->get() as $d) {
            $this->assertStringContainsString('href="'.route('destinations.show', $d->slug).'"', $html, "{$d->name} is in the menu");
        }
        $this->assertGreaterThanOrEqual(6, TravelStyle::active()->count());
        foreach (TravelStyle::active()->get() as $s) {
            $this->assertStringContainsString('href="'.route('travel-styles.show', $s->slug).'"', $html, "{$s->name} is in the menu");
        }
        $this->assertStringContainsString('href="'.route('destinations.index').'"', $html);
        $this->assertStringContainsString('href="'.route('travel-styles.index').'"', $html);
    }

    public function test_every_destination_has_its_own_page_with_the_quiz_its_trips_and_a_closing_band(): void
    {
        foreach (Experience::active()->get() as $d) {
            $html = $this->get(route('destinations.show', $d->slug))->assertOk()->getContent();

            $this->assertSame(1, substr_count($html, '<h1'), "{$d->slug} needs exactly one h1");
            $this->assertStringContainsString('Explore '.$d->name, strip_tags($html));
            $this->assertStringContainsString('id="quiz"', $html);
            $this->assertStringContainsString('<link rel="canonical"', $html);
            $this->assertStringContainsString('BreadcrumbList', $html);
            $this->assertStringContainsString('aria-labelledby="cta-title"', $html, 'the closing band');
            $this->assertSame($d->packages()->published()->count(), substr_count($html, 'class="pkg-card group"'), "{$d->slug}: one card per published trip");
        }
        $this->get('/destinations/atlantis')->assertNotFound();
    }

    public function test_every_travel_style_has_its_own_page_with_the_quiz_its_trips_and_the_other_styles(): void
    {
        foreach (TravelStyle::active()->get() as $s) {
            $html = $this->get(route('travel-styles.show', $s->slug))->assertOk()->getContent();

            $this->assertSame(1, substr_count($html, '<h1'));
            $this->assertStringContainsString('id="quiz"', $html);
            $this->assertStringContainsString(e($s->intro), $html, 'the intro paragraph');
            $this->assertSame($s->packages()->published()->count(), substr_count($html, 'class="pkg-card group"'));
            $this->assertStringContainsString('aria-label="Other travel styles"', $html);
        }
        $this->get('/travel-styles/nope')->assertNotFound();
        $this->get('/travel-styles')->assertOk()->assertSee('All Our', false)->assertSee('Travel Styles');
    }

    public function test_starter_styles_are_linked_to_real_trips_and_destinations_have_an_intro(): void
    {
        $this->assertGreaterThan(0, TravelStyle::whereHas('packages')->count());
        $this->assertTrue(TravelStyle::where('slug', 'medieval-cities')->firstOrFail()->packages()->where('title', 'like', '%Bruges%')->exists());
        $this->assertSame(0, Experience::whereNull('intro')->count());
    }

    public function test_pages_stay_out_of_the_menu_and_the_sitemap_when_switched_off_and_the_sitemap_lists_the_rest(): void
    {
        $off = TravelStyle::where('slug', 'beer-abbeys')->firstOrFail();
        $off->update(['active' => false]);

        $this->get(route('travel-styles.show', 'beer-abbeys'))->assertNotFound();
        $this->get('/packages')->assertDontSee('href="'.url('/travel-styles/beer-abbeys').'"', false);
        $map = $this->get('/sitemap.xml')->assertOk()->getContent();
        $this->assertStringNotContainsString('/travel-styles/beer-abbeys', $map);
        $this->assertStringContainsString('/travel-styles/medieval-cities', $map);
        $this->assertStringContainsString('/destinations/bruges', $map);
        $this->assertStringContainsString('/travel-styles<', $map);
    }

    public function test_the_admin_can_manage_travel_styles_and_assign_them_to_a_package(): void
    {
        $this->actingAs(AdminUser::firstOrFail());

        $this->get('/admin/travel-styles')->assertOk()->assertSee('Medieval Cities');
        $this->get('/admin/travel-styles/create')->assertOk();
        $this->post('/admin/travel-styles', ['name' => 'Family Trips', 'description' => 'Easy days for all ages.', 'intro' => 'Slow days with something for everyone.', 'sort' => 9, 'active' => '1'])->assertRedirect('/admin/travel-styles');
        $new = TravelStyle::where('slug', 'family-trips')->firstOrFail();
        $this->get("/admin/travel-styles/{$new->id}/edit")->assertOk();
        $this->get('/travel-styles/family-trips')->assertOk()->assertSee('Slow days with something for everyone.');
        $this->get('/packages')->assertSee('href="'.route('travel-styles.show', 'family-trips').'"', false);

        $package = Package::firstOrFail();
        $this->put("/admin/packages/{$package->id}", [
            'title' => $package->title, 'nights' => $package->nights, 'currency' => 'EUR', 'is_published' => '1',
            'travel_style_ids' => [$new->id],
        ])->assertSessionHasNoErrors();
        $this->assertSame([$new->id], $package->fresh()->travelStyles->pluck('id')->all());
        $this->get('/travel-styles/family-trips')->assertSee($package->title);
        $this->get("/admin/packages/{$package->id}/edit")->assertOk()->assertSee('Travel styles this tour fits');

        // a destination's own page text is editable too
        $d = Experience::where('slug', 'bruges')->firstOrFail();
        $this->put("/admin/experiences/{$d->id}", ['name' => 'Bruges', 'slug' => 'bruges', 'description' => 'Short line.', 'intro' => 'A fresh intro for Bruges.', 'sort' => 1, 'active' => '1'])->assertRedirect();
        $this->get('/destinations/bruges')->assertSee('A fresh intro for Bruges.');
    }
}