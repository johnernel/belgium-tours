<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Page;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InfoPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_the_about_us_menu_has_four_cards_that_link_to_their_pages(): void
    {
        $html = $this->get('/packages')->assertOk()->getContent();
        $start = strpos($html, 'id="mega-about"');
        $this->assertNotFalse($start);
        $menu = substr($html, $start, strpos($html, 'id="mobile-nav"', $start) - $start);

        $this->assertStringContainsString('Plan', $menu);
        foreach (['about' => 'About Us', 'team' => 'Meet Our Team', 'testimonials' => 'Testimonials', 'faqs' => 'FAQs'] as $route => $label) {
            $this->assertStringContainsString('href="'.route($route).'"', $menu);
            $this->assertStringContainsString($label, $menu);
        }
        $this->assertSame(4, substr_count($menu, 'Learn more'));
        // the nav item itself now reads "About Us" (once as the button, never the old "About" link)
        $this->assertStringContainsString('About Us', substr($html, 0, $start));
    }

    public function test_each_new_page_is_a_photo_hero_with_the_main_quiz_form(): void
    {
        foreach (['team' => 'Meet Our', 'testimonials' => 'What Travellers', 'faqs' => 'Frequently Asked'] as $route => $h1) {
            $html = $this->get(route($route))->assertOk()->getContent();

            $this->assertStringContainsString('id="quiz"', $html, "$route has the quiz");
            $this->assertStringContainsString('Belgium, Tailored to You in 30 Seconds', strip_tags($html));
            $this->assertStringContainsString($h1, strip_tags($html));
            $this->assertStringContainsString('nav-overlay', $html, "$route uses the transparent header over its photo");
        }
    }

    public function test_the_about_page_has_the_same_photo_hero_with_the_main_quiz_form(): void
    {
        $html = $this->get('/about')->assertOk()->getContent();

        $this->assertStringContainsString('id="quiz"', $html);
        $this->assertStringContainsString('Belgium, Tailored to You in 30 Seconds', strip_tags($html));
        $this->assertStringContainsString('nav-overlay', $html);
        $this->assertStringContainsString('About', strip_tags($html));
    }

    public function test_sample_team_and_testimonials_are_hidden_from_google_until_real_ones_exist(): void
    {
        $this->get(route('team'))->assertSee('Sample team', false)->assertSee('noindex', false);
        $this->assertStringNotContainsString(route('team'), $this->get('/sitemap.xml')->getContent());

        TeamMember::query()->update(['is_placeholder' => false]);
        Page::where('slug', 'team')->update(['noindex' => false]);
        $this->get(route('team'))->assertDontSee('Sample team', false);
        $this->assertStringContainsString(route('team'), $this->get('/sitemap.xml')->getContent());

        Testimonial::query()->update(['is_placeholder' => false]);
        $this->assertStringContainsString(route('testimonials'), $this->get('/sitemap.xml')->getContent());
        $this->assertStringContainsString(route('faqs'), $this->get('/sitemap.xml')->getContent());
    }

    public function test_the_faq_page_lists_every_question_with_structured_data(): void
    {
        $html = $this->get(route('faqs'))->assertOk()->getContent();

        $this->assertStringContainsString('"FAQPage"', $html);
        $this->assertSame(\App\Models\Faq::active()->count(), substr_count($html, '<details name="faq-page"'));
    }

    public function test_the_testimonials_page_lists_active_testimonials_only(): void
    {
        Testimonial::create(['name' => 'Hidden Person', 'quote' => 'Should not show', 'active' => false, 'sort' => 99]);

        $this->get(route('testimonials'))->assertOk()->assertDontSee('Hidden Person')->assertSee(Testimonial::active()->first()->name);
    }

    public function test_admin_can_manage_team_members_and_edit_the_new_pages(): void
    {
        $this->actingAs(AdminUser::where('email', 'admin@test.example')->firstOrFail());

        $this->get('/admin/team')->assertOk();
        $this->post('/admin/team', ['name' => 'Real Person', 'role' => 'Trip planner', 'bio' => 'Hello.', 'sort' => 1, 'active' => '1'])->assertRedirect();
        $this->assertDatabaseHas('team_members', ['name' => 'Real Person', 'role' => 'Trip planner']);

        $page = Page::bySlug('team');
        $this->get("/admin/pages/{$page->id}/edit")->assertOk()->assertSee('Banner at the top of the page');
        $this->put("/admin/pages/{$page->id}", ['title' => 'Meet Our Team', 'content' => ['hero_headline' => 'Say *hello*', 'hero_sub' => 'New words.']])->assertRedirect();
        $this->get(route('team'))->assertSee('Say', false)->assertSee('New words.');
    }
}
