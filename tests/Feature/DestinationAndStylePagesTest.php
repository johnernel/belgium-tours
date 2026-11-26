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

    public function test_the_header_menus_show_the_first_four_destinations_and_styles_and_link_to_all_of_them(): void
    {
        $html = $this->get('/packages')->assertOk()->getContent();

        $this->assertStringContainsString('aria-controls="mega-destinations-test"', $html);
        $this->assertStringContainsString('aria-controls="mega-styles-test"', $html);
        $menu = fn (string $id) => substr($html, strpos($html, 'id="'.$id.'"'), 12000);

        $this->assertGreaterThanOrEqual(6, Experience::active()->count());
        $destMenu = $menu('mega-destinations-test');
        $this->assertSame(4, substr_count(substr($destMenu, 0, strpos($destMenu, 'Plan your journey')), '/destinations/'), 'four destination cards');
        foreach (Experience::active()->take(4)->get() as $d) {
            $this->assertStringContainsString('href="'.route('destinations.show', $d->slug).'"', $destMenu, "{$d->name} is in the menu");
        }
        $this->assertStringNotContainsString('href="'.route('destinations.show', Experience::active()->get()[4]->slug).'"', $destMenu, 'the fifth is only reachable through "view all"');

        $this->assertGreaterThanOrEqual(6, TravelStyle::active()->count());
        $styleMenu = $menu('mega-styles-test');
        $this->assertSame(4, substr_count(substr($styleMenu, 0, strpos($styleMenu, 'Tailor your journey')), '/travel-styles/'), 'four travel style cards, same layout as the destinations');
        foreach (TravelStyle::active()->take(4)->get() as $s) {
            $this->assertStringContainsString('href="'.route('travel-styles.show', $s->slug).'"', $styleMenu, "{$s->name} is in the menu");
        }
        $this->assertStringContainsString('href="'.route('destinations.index').'"', $html);
        $this->assertStringContainsString('href="'.route('travel-styles.index').'"', $html);

        // the overview pages list every one
        $this->get('/destinations')->assertOk()->assertSee(Experience::active()->get()[5]->name);
        foreach (TravelStyle::active()->get() as $s) {
            $this->get('/travel-styles')->assertSee('href="'.route('travel-styles.show', $s->slug).'"', false);
        }
    }

    public function test_the_menu_strip_button_is_the_orange_cta_and_nothing_sits_behind_it(): void
    {
        $html = $this->get('/packages')->assertOk()->getContent();
        foreach (['mega-destinations-test', 'mega-styles-test'] as $id) {
            $start = strpos($html, 'id="'.$id.'"');
            $menu = substr($html, $start, strpos($html, 'Get a free quote', $start) > 0 && $id === 'mega-destinations-test' ? strpos($html, 'Get a free quote', $start) - $start + 20 : strpos($html, 'Plan your trip', $start) - $start + 20);
            $this->assertStringContainsString('bg-cta text-white hover:bg-cta-hover', $menu, 'orange #C94B0E, green #30483A on hover');
            $this->assertStringNotContainsString('-scale-x-100', $menu, 'the mirrored sketch behind the button is gone');
        }
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
        $this->get('/travel-styles')->assertSee('href="'.route('travel-styles.show', 'family-trips').'"', false);

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

    public function test_the_edit_screens_have_a_tours_list_that_sets_the_links_and_the_tours_column(): void
    {
        $this->actingAs(AdminUser::firstOrFail());
        $a = Package::orderBy('id')->first();
        $b = Package::orderBy('id')->skip(1)->first();

        foreach ([['travel-styles', TravelStyle::where('slug', 'food-chocolate')->firstOrFail(), 'travelStyles'], ['experiences', Experience::where('slug', 'leuven')->firstOrFail(), 'experiences']] as [$screen, $record, $relation]) {
            // (the previous loop ended with a refused save, whose submitted values the next page would show once, so look at the list first)
            $this->get("/admin/$screen")->assertOk();
            // the list is on the edit screen with the current links ticked
            $html = $this->get("/admin/$screen/{$record->id}/edit")->assertOk()->assertSee('Tours')->getContent();
            $this->assertStringContainsString('name="package_ids[]"', $html);
            $this->assertStringContainsString('name="package_ids_present"', $html);
            foreach ($record->packages as $linked) {
                $this->assertMatchesRegularExpression('~value="'.$linked->id.'"[^>]*checked~', $html, "{$linked->title} is ticked");
            }

            $base = ['name' => $record->name, 'slug' => $record->slug, 'sort' => $record->sort, 'active' => '1', 'package_ids_present' => '1'];

            // tick exactly two tours: the links and the Tours column follow
            $this->put("/admin/$screen/{$record->id}", $base + ['package_ids' => [$a->id, $b->id]])->assertSessionHasNoErrors();
            $this->assertEqualsCanonicalizing([$a->id, $b->id], $record->fresh()->packages()->pluck('packages.id')->all());
            $this->assertMatchesRegularExpression('~'.preg_quote(e($record->name), '~').'</td>\s*<td class="p-3">2</td>~', $this->get("/admin/$screen")->assertOk()->getContent(), 'the Tours column shows 2');

            // a save from a form that did not carry the list leaves the links alone
            $this->put("/admin/$screen/{$record->id}", ['name' => $record->name, 'slug' => $record->slug, 'sort' => $record->sort, 'active' => '1'])->assertSessionHasNoErrors();
            $this->assertSame(2, $record->fresh()->packages()->count());

            // unticking everything on the list clears the links
            $this->put("/admin/$screen/{$record->id}", $base)->assertSessionHasNoErrors();
            $this->assertSame(0, $record->fresh()->packages()->count());

            // a tour that does not exist is refused
            $this->put("/admin/$screen/{$record->id}", $base + ['package_ids' => [999999]])->assertSessionHasErrors('package_ids.0');
        }

        // the same list when adding a new one
        $this->get('/admin/travel-styles/create')->assertOk()->assertSee('name="package_ids[]"', false);
        $this->post('/admin/travel-styles', ['name' => 'Romantic Escapes', 'sort' => 9, 'active' => '1', 'package_ids_present' => '1', 'package_ids' => [$a->id]])->assertRedirect('/admin/travel-styles');
        $this->assertSame([$a->id], TravelStyle::where('slug', 'romantic-escapes')->firstOrFail()->packages()->pluck('packages.id')->all());
    }
}