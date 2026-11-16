<?php

namespace Tests\Feature;

use App\Models\TourType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourTypePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_each_tour_type_has_a_page_with_headline_description_and_quiz(): void
    {
        foreach (TourType::all() as $type) {
            $html = $this->get(route('tours.show', $type->slug))->assertOk()->getContent();

            $this->assertSame(1, substr_count($html, '<h1'), $type->slug.' needs exactly one h1');
            $this->assertStringContainsString($type->headline, $html);
            $this->assertStringContainsString($type->intro, $html);
            $this->assertStringContainsString('id="quiz"', $html, 'the quiz sits beside the headline');
            $this->assertStringContainsString('<link rel="canonical"', $html);
            $this->assertStringContainsString('BreadcrumbList', $html);
        }
    }

    public function test_the_quiz_skips_the_travel_style_question_and_sends_the_tour_type(): void
    {
        $html = $this->get(route('tours.show', 'chauffeur-driven'))->assertOk()->getContent();

        // preset answer for the "favourite way to see a country" step, and the tour type name travels with the enquiry
        $this->assertMatchesRegularExpression('/x-data="quiz\(.*?\),\s*JSON\.parse\(\'[^\']*travel_style[^\']*chauffeur/s', $html);
        $this->assertStringContainsString('Chauffeur Driven', $html);
    }

    public function test_unknown_or_inactive_tour_types_are_404(): void
    {
        $this->get('/tours/does-not-exist')->assertNotFound();

        TourType::where('slug', 'coach-tours')->update(['active' => false]);
        $this->get('/tours/coach-tours')->assertNotFound();
    }

    public function test_the_no_javascript_dropdown_fallback_redirects(): void
    {
        $this->get('/tours?type=self-drive')->assertRedirect(route('tours.show', 'self-drive'));
        $this->get('/tours?type=nonsense')->assertRedirect(route('packages.index'));
        $this->get('/tours')->assertRedirect(route('packages.index'));
    }

    public function test_the_nav_and_hero_dropdown_link_to_the_tour_type_pages(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Tour Types', $html);
        foreach (['self-drive', 'chauffeur-driven', 'coach-tours'] as $slug) {
            $this->assertStringContainsString(route('tours.show', $slug), $html);
            $this->assertStringContainsString('data-url="'.route('tours.show', $slug).'"', $html);
        }
    }

    public function test_tour_type_pages_are_in_the_sitemap(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertSee(route('tours.show', 'self-drive'), false);
    }

    public function test_about_and_contact_have_photo_heroes(): void
    {
        foreach (['/about', '/contact'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('fetchpriority="high"', $html, "$url hero photo");
            $this->assertStringContainsString('nav-overlay', $html, "$url header sits over the photo");
        }
    }

    public function test_pages_without_a_hero_keep_the_solid_header(): void
    {
        $this->get('/packages')->assertOk()->assertDontSee('nav-overlay');
    }

    public function test_admin_can_edit_tour_type_page_copy(): void
    {
        $admin = \App\Models\AdminUser::firstOrFail();
        $type = TourType::where('slug', 'self-drive')->firstOrFail();

        $this->actingAs($admin)->put("/admin/tour-types/{$type->id}", [
            'name' => 'Self Drive', 'headline' => 'A brand new headline', 'intro' => 'A brand new description.', 'sort' => 1, 'active' => '1',
        ])->assertRedirect();
        $this->get('/tours/self-drive')->assertSee('A brand new headline')->assertSee('A brand new description.');

        $about = \App\Models\Page::bySlug('about');
        $this->actingAs($admin)->put("/admin/pages/{$about->id}", [
            'title' => 'About Us', 'content' => ['hero_sub' => 'Banner words from admin'],
        ])->assertRedirect();
        $this->get('/about')->assertSee('Banner words from admin');
    }
}
