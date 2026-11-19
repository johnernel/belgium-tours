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

    public function test_on_phones_two_sections_are_hidden_and_the_ways_cards_become_a_snap_carousel_with_dots(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('~<section class="[^"]*max-md:hidden[^"]*" aria-label="Why travel with us">~', $html, 'feature strip hidden on phones');
        $this->assertMatchesRegularExpression('~<section class="[^"]*max-md:hidden[^"]*" aria-labelledby="ht-about">~', $html, 'tailor-made section hidden on phones');
        $this->assertStringContainsString('aria-label="Ways to travel, swipe to see more"', $html);
        $this->assertStringContainsString('snap-x snap-mandatory', $html);
        $this->assertSame(3, substr_count($html, 'w-[calc(100vw-2rem)]'), 'three cards, each 100vw minus 2rem so the next one peeks');
        $this->assertStringContainsString('aria-label="Choose a card"', $html, 'pagination dots');
        $this->assertStringContainsString('aria-label="Previous card"', $html, 'previous arrow');
        $this->assertStringContainsString('aria-label="Next card"', $html, 'next arrow');
        $this->assertStringContainsString('Our Packages', $html, 'the packages section label');
        $this->assertStringNotContainsString('Signature journeys', $html);
        $this->assertStringContainsString('w-[calc(100%-2rem)] shrink-0 snap-start', $html, 'package cards leave a 20px peek on phones');
        $this->assertStringContainsString('whitespace-nowrap font-display text-xl sm:text-3xl', $html, 'flag headings stay on one line on phones');
    }

    public function test_on_phones_the_package_card_is_horizontal_and_view_all_moves_below_each_carousel(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('max-sm:flex-row max-sm:rounded-none max-sm:p-0', $html, 'horizontal square card on phones');
        $this->assertStringContainsString('max-sm:hidden">View all', $html, 'the heading View all is hidden on phones');
        $this->assertSame(3, substr_count($html, 'View all packages'), 'one View all packages link under each of the three carousels');
        $this->assertStringContainsString('sm:hidden">View all packages', $html, 'and they only show on phones');
    }

    public function test_phone_typography_and_the_centred_why_travel_with_us_text(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('max-md:justify-center max-md:text-center', $html, 'the Why Travel With Us block is centred on phones');
        $this->assertStringContainsString('max-md:items-center max-md:text-center', $html, 'and so is its small label');
        $this->assertStringContainsString('text-[2.65rem]', $html, 'the hero headline is smaller on phones');

        $css = file_get_contents(base_path('resources/css/brand.css'));
        $this->assertMatchesRegularExpression('~@media \(max-width: 639\.98px\) \{\s*:root \{\s*--text-heading: 44px;~', $css, 'section headings are 44px on phones');
        $this->assertStringContainsString('--text-heading: 50px;', $css, 'and still 50px from the sm breakpoint up');
    }
}