<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** /homepage-test now holds the PREVIOUS home design (kept for comparison and rollback); / holds the redesigned page. */
class HomepageTestPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_the_previous_design_lives_at_homepage_test_hidden_from_search_with_the_normal_header_and_footer(): void
    {
        $this->get('/homepage-test')->assertOk()
            ->assertSee('noindex', false)
            ->assertSee('Homepage Test (previous design)')
            ->assertSee('id="quiz"', false)
            ->assertSee('The Journey', false)          // the journey map section of the previous design
            ->assertDontSee('Plan Your Belgium Trip', false);
        $this->get('/sitemap.xml')->assertDontSee('homepage-test');
    }

    public function test_the_main_home_page_has_the_redesigned_layout_and_is_indexable(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Enquire Now')
            ->assertSee('Why travel with us', false)
            ->assertSee('Find your style', false)
            ->assertSee('mega-tours-test', false)
            ->assertSee('images/logo', false)
            ->assertDontSee('noindex', false)
            ->assertDontSee('Homepage Test', false);
    }

    public function test_both_pages_keep_the_quiz_the_quote_popup_and_the_forms_working(): void
    {
        foreach (['/', '/homepage-test'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('id="quiz"', $html, "$url quiz");
            $this->assertStringContainsString('modal-title', $html, "$url quote popup");
            $this->assertSame(1, substr_count($html, '<h1'), "$url one h1");
        }
    }
}