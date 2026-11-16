<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Experience;
use App\Models\Package;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestinationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_home_has_travel_your_way_tabs_linking_to_each_tour_type_page_after_destinations(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<em class="heading-accent">Your Way</em>', $html);
        $section = substr($html, strpos($html, 'aria-labelledby="way-title"'), 20000);
        $this->assertSame(3, substr_count($section, 'role="tab"'));
        $this->assertSame(3, substr_count($section, 'role="tabpanel"'));
        foreach (['self-drive', 'chauffeur-driven', 'coach-tours'] as $slug) {
            $this->assertStringContainsString('href="'.route('tours.show', $slug).'"', $section);
        }
        $this->assertLessThan(strpos($html, 'aria-labelledby="way-title"'), strpos($html, 'aria-labelledby="dest-title"'));
        $this->assertLessThan(strpos($html, 'aria-labelledby="how-title"'), strpos($html, 'aria-labelledby="way-title"'));
    }

    public function test_the_six_destinations_are_seeded_with_real_tour_counts(): void
    {
        $this->assertSame(['Bruges', 'Ghent', 'Brussels', 'Antwerp', 'Leuven', 'Ardennes'], Experience::active()->pluck('name')->all());

        $bruges = Experience::where('slug', 'bruges')->withCount('packages')->first();
        $expected = Package::where('route', 'like', '%Bruges%')->count();
        $this->assertGreaterThan(0, $expected);
        $this->assertSame($expected, $bruges->packages_count);
    }

    public function test_home_has_the_destinations_accordion_with_six_panels_and_the_cta(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Where we go', $html);
        $this->assertStringContainsString('<em class="heading-accent">slowing down</em>', $html);
        $this->assertStringContainsString('View All Destinations', $html);
        $this->assertStringContainsString('href="'.route('destinations.index').'"', $html);

        $section = substr($html, strpos($html, 'aria-labelledby="dest-title"'), 30000);
        $this->assertSame(6, substr_count($section, ':aria-expanded="open === '), 'one button per panel');
        foreach (['Bruges', 'Ghent', 'Brussels', 'Antwerp', 'Leuven', 'Ardennes'] as $city) {
            $this->assertStringContainsString('aria-label="View '.$city.' tours"', $section, "$city arrow link");
        }
        $this->assertMatchesRegularExpression('/\d+ tours?<\/p>/', $section, 'tour count on each panel');
        $this->assertStringContainsString('lg:grow-[4.6]', $section, 'desktop expanded flex ratio');
        $this->assertStringContainsString('h-[360px]', $section, 'mobile expanded height');
        $this->assertStringContainsString('h-20', $section, 'mobile collapsed height (80px)');
    }

    public function test_the_section_sits_between_ways_to_travel_and_how_it_works(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertLessThan(strpos($html, 'aria-labelledby="dest-title"'), strpos($html, 'aria-labelledby="ways-title"'));
        $this->assertLessThan(strpos($html, 'aria-labelledby="how-title"'), strpos($html, 'aria-labelledby="dest-title"'));
    }

    public function test_the_destinations_page_has_a_photo_hero_with_the_quiz_and_a_card_per_destination(): void
    {
        $html = $this->get('/destinations')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('id="quiz"', $html, 'quiz on top of the hero');
        $this->assertStringContainsString('nav-overlay', $html, 'transparent header over the photo');
        $this->assertStringContainsString('class="heading-accent"', $html);
        $this->assertStringContainsString('<link rel="canonical"', $html);
        $this->assertStringContainsString('BreadcrumbList', $html);
        $this->assertSame(6, substr_count($html, 'class="group flex w-full flex-col'), 'a card for every destination');

        foreach (Experience::withCount(['packages' => fn ($q) => $q->where('is_published', true)])->get() as $e) {
            $this->assertStringContainsString('>'.e($e->name).'</h3>', $html);
            $this->assertStringContainsString($e->packages_count === 1 ? '1 tour<' : $e->packages_count.' tours<', $html);
            $this->assertStringContainsString('href="'.route('packages.index', ['experience' => $e->slug]).'"', $html);
        }
    }

    public function test_unpublished_tours_are_not_counted_and_inactive_destinations_are_hidden(): void
    {
        $before = Experience::where('slug', 'ghent')->withCount(['packages' => fn ($q) => $q->where('is_published', true)])->first()->packages_count;
        Package::where('route', 'like', '%Ghent%')->first()->update(['is_published' => false]);
        $after = Experience::where('slug', 'ghent')->withCount(['packages' => fn ($q) => $q->where('is_published', true)])->first()->packages_count;
        $this->assertSame($before - 1, $after);

        Experience::where('slug', 'leuven')->update(['active' => false]);
        $this->get('/')->assertDontSee('View Leuven tours', false);
        $this->get('/destinations')->assertDontSee('>Leuven</h3>', false);
    }

    public function test_packages_can_be_filtered_by_destination(): void
    {
        $expected = Package::published()->whereHas('experiences', fn ($q) => $q->where('slug', 'bruges'))->count();
        $this->assertGreaterThan(0, $expected);

        $html = $this->get('/packages?experience=bruges')->assertOk()->getContent();

        $this->assertSame($expected, substr_count($html, '<article'));
        $this->assertStringNotContainsString('Brussels &amp; Leuven Weekend Drive', $html);
    }

    public function test_destinations_page_is_in_the_sitemap_and_footer(): void
    {
        $this->get('/sitemap.xml')->assertSee(route('destinations.index'), false);
        $this->get('/')->assertSee('href="'.route('destinations.index').'"', false);
    }

    public function test_admin_can_manage_destinations_and_assign_tours(): void
    {
        $admin = AdminUser::firstOrFail();
        $this->actingAs($admin);

        $this->get('/admin/experiences')->assertOk()->assertSee('Bruges');
        $this->get('/admin/experiences/create')->assertOk();

        $this->post('/admin/experiences', ['name' => 'Mechelen', 'description' => 'A quiet city.', 'image_focus' => '40% 30%', 'sort' => 9, 'active' => '1'])->assertRedirect('/admin/experiences');
        $new = Experience::where('slug', 'mechelen')->firstOrFail();
        $this->assertSame('40% 30%', $new->image_focus);
        $this->get("/admin/experiences/{$new->id}/edit")->assertOk();
        $this->get('/destinations')->assertSee('>Mechelen</h3>', false);

        $package = Package::firstOrFail();
        $this->put("/admin/packages/{$package->id}", [
            'title' => $package->title, 'nights' => $package->nights, 'currency' => 'EUR', 'is_published' => '1',
            'experience_ids' => [$new->id],
        ])->assertSessionHasNoErrors();
        $this->assertSame([$new->id], $package->fresh()->experiences->pluck('id')->all());
        $this->get('/packages?experience=mechelen')->assertSee($package->title);
    }

    public function test_focus_point_must_be_two_percentages(): void
    {
        $this->actingAs(AdminUser::firstOrFail())
            ->post('/admin/experiences', ['name' => 'Bad focus', 'image_focus' => 'left top', 'sort' => 1])
            ->assertSessionHasErrors('image_focus');
    }

    public function test_admin_can_edit_the_destinations_page_banner(): void
    {
        $admin = AdminUser::firstOrFail();
        $page = Page::bySlug('destinations');

        $this->actingAs($admin)->get("/admin/pages/{$page->id}/edit")->assertOk()->assertSee('Banner at the top of the page');
        $this->put("/admin/pages/{$page->id}", ['title' => 'Destinations', 'content' => ['hero_headline' => 'Go *somewhere* slow', 'hero_sub' => 'Banner words.']])->assertRedirect();

        $this->get('/destinations')->assertSee('<em class="heading-accent">somewhere</em>', false)->assertSee('Banner words.');
    }
}
