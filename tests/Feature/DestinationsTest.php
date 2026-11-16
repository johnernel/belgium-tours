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

    public function test_home_has_the_top_destinations_section_with_five_tabs_and_the_cta(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Top Destinations', $html);
        $this->assertStringContainsString('<em class="heading-accent">slowing down</em>', $html);
        foreach (['Chocolate &amp; Pralines', 'Beer &amp; Abbeys', 'Art &amp; Architecture', 'Flanders Fields History', 'Canals &amp; Cycling'] as $name) {
            $this->assertStringContainsString($name, $html);
        }
        $this->assertSame(5, substr_count($html, 'role="tabpanel" aria-labelledby="dtab-'), 'one panel per destination');
        $this->assertStringContainsString('aria-orientation="vertical"', $html);
        $this->assertStringContainsString('View All Destinations', $html);
        $this->assertStringContainsString('href="'.route('destinations.index').'"', $html);
        $this->assertStringContainsString('View tours', $html);
    }

    public function test_the_section_sits_after_the_ways_to_travel_section(): void
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
        $this->assertSame(Experience::count(), substr_count($html, 'class="group flex w-full flex-col'), 'a card for every destination');
        foreach (Experience::all() as $e) {
            $this->assertStringContainsString(e($e->name), $html);
            $this->assertStringContainsString(e($e->description), $html);
            $this->assertStringContainsString('href="'.route('packages.index', ['experience' => $e->slug]).'"', $html);
        }
    }

    public function test_inactive_destinations_are_hidden_everywhere(): void
    {
        Experience::where('slug', 'beer-abbeys')->update(['active' => false]);

        $this->get('/')->assertDontSee('dtab-beer-abbeys', false);
        $this->get('/destinations')->assertDontSee('Beer &amp; Abbeys', false);
    }

    public function test_packages_can_be_filtered_by_destination(): void
    {
        $expected = Package::published()->whereHas('experiences', fn ($q) => $q->where('slug', 'flanders-fields-history'))->count();
        $this->assertGreaterThan(0, $expected);

        $html = $this->get('/packages?experience=flanders-fields-history')->assertOk()->getContent();

        $this->assertSame($expected, substr_count($html, '<article'));
        $this->assertStringContainsString('Flanders Fields', $html);
        $this->assertStringNotContainsString('Brussels Art Nouveau &amp; Chocolate Day', $html);
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

        $this->get('/admin/experiences')->assertOk()->assertSee('Chocolate');
        $this->get('/admin/experiences/create')->assertOk();

        $this->post('/admin/experiences', ['name' => 'Walking & Wellness', 'description' => 'Slow walks.', 'sort' => 9, 'active' => '1'])->assertRedirect('/admin/experiences');
        $new = Experience::where('slug', 'walking-wellness')->firstOrFail();
        $this->get("/admin/experiences/{$new->id}/edit")->assertOk();
        $this->get('/destinations')->assertSee('Walking &amp; Wellness', false);

        $package = Package::firstOrFail();
        $this->put("/admin/packages/{$package->id}", [
            'title' => $package->title, 'nights' => $package->nights, 'currency' => 'EUR', 'is_published' => '1',
            'experience_ids' => [$new->id],
        ])->assertSessionHasNoErrors();
        $this->assertSame([$new->id], $package->fresh()->experiences->pluck('id')->all());
        $this->get('/packages?experience=walking-wellness')->assertSee($package->title);
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
