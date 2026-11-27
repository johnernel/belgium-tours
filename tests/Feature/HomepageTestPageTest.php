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
            ->assertSee('Inquire Now')
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
        $this->assertMatchesRegularExpression('~<section [^>]*class="[^"]*max-md:hidden[^"]*" aria-labelledby="ht-about">~', $html, 'tailor-made section hidden on phones');
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

    public function test_package_cards_share_one_layout_and_view_all_moves_below_each_carousel_on_phones(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('class="pkg-card group"', $html, 'every package card uses the one shared card');
        $this->assertStringNotContainsString('max-sm:flex-row', $html, 'no separate phone layout for the card');
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

    public function test_on_phones_the_hero_text_is_centred_and_the_description_is_hidden(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('lg:pt-24 max-sm:text-center', $html);
        $this->assertStringContainsString('max-sm:-translate-x-1/2', $html, 'the orange underline under the headline is centred too');
        $this->assertMatchesRegularExpression('~<p class="hero-rise hero-rise-2 mt-9 [^"]*max-sm:hidden~', $html, 'the description paragraph is hidden on phones only');
    }

    public function test_on_phones_the_why_travel_with_us_cards_become_a_snap_carousel_with_dots(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('aria-label="Why travel with us, swipe to see more"', $html);
        $this->assertSame(4, substr_count($html, 'max-sm:w-[calc(100%-2rem)] max-sm:shrink-0 max-sm:snap-start border') + substr_count($html, 'shadow-[0_8px_22px_-14px_var(--color-mist-deep)] max-sm:w-[calc(100%-2rem)] max-sm:shrink-0 max-sm:snap-start'), 'four cards, each leaving a 20px peek on phones');
        $this->assertStringContainsString('max-sm:snap-x max-sm:snap-mandatory', $html);
        $this->assertStringContainsString('sm:grid-cols-2', $html, 'the 2x2 grid from sm up is kept');
    }

    public function test_on_phones_the_stress_free_cards_become_a_snap_carousel_with_dots(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('aria-label="Stress-free travel, swipe to see more"', $html);
        $this->assertSame(3, substr_count($html, 'max-md:w-[calc(100%-2rem)] max-md:shrink-0 max-md:snap-start'), 'three cards, each leaving a 20px peek');
        $this->assertStringContainsString('md:grid-cols-3', $html, 'the three equal columns from md up are kept');
    }

    public function test_on_phones_the_testimonial_cards_leave_a_20px_peek_of_the_next_card(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('flex w-[calc(100%-2rem)] shrink-0 snap-start flex-col rounded-md bg-white p-7', $html, 'testimonial cards are 100% - 2rem wide on phones');
        $this->assertStringContainsString('sm:w-[calc((100%-1.5rem)/2)] lg:w-[calc((100%-3rem)/3)]', $html, 'tablet and desktop widths are kept');
        $this->assertStringContainsString('max-sm:after:w-5', $html, 'a trailing spacer lets the last card scroll fully into view');
    }

    public function test_on_phones_the_destination_cards_become_a_snap_carousel(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('aria-label="Destinations, swipe to see more"', $html);
        $this->assertSame(6, substr_count($html, 'max-sm:w-[calc(100%-2rem)] max-sm:shrink-0 max-sm:snap-start sm:p-6'), 'six destination cards, each leaving a 20px peek');
        $this->assertStringContainsString('sm:gap-6 lg:grid-cols-3', $html, 'the grid from sm up is kept');
        $this->assertStringContainsString('aria-label="Choose a destination"', $html, 'pagination dots for the phone carousel');
    }

    public function test_on_phones_the_payment_cards_become_a_snap_carousel_with_dots(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('aria-label="Payment options, swipe to see more"', $html);
        $this->assertSame(4, substr_count($html, 'max-sm:w-[calc(100%-2rem)] max-sm:shrink-0 max-sm:snap-start xl:px-4'), 'four cards, each leaving a 20px peek');
        $this->assertStringContainsString('aria-label="Choose a payment option"', $html, 'pagination dots');
        $this->assertStringContainsString('sm:grid-cols-2 xl:grid-cols-4', $html, 'the grid from sm up is kept');
    }

    public function test_the_header_strip_shows_the_contact_details_instead_of_the_old_tagline(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('href="tel:+10000000000"', $html);
        $this->assertStringContainsString('+1 000-000-0000', $html);
        $this->assertStringContainsString('href="mailto:info@gobelgiumtrips.com"', $html);
        $this->assertStringNotContainsString('Free, no-obligation quote. Tailor-made trips across Belgium.', $html);
    }

    public function test_the_header_has_destinations_and_travel_styles_mega_menus_but_no_packages_item(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $header = substr($html, strpos($html, '<header'), strpos($html, '</header>') - strpos($html, '<header'));
        $this->assertStringContainsString('aria-controls="mega-destinations-test"', $header, 'the Destinations mega menu');
        $this->assertStringContainsString('aria-controls="mega-styles-test"', $header, 'the Travel Styles mega menu');
        $this->assertStringNotContainsString('>Packages</a>', $header);
        $this->assertStringContainsString('>Home</a>', $header);
        $this->assertStringContainsString('>Contact Us</a>', $header);
        $this->assertStringContainsString('aria-controls="mega-tours-test"', $header, 'the Tour Types mega menu is still there');
    }

    public function test_the_four_menu_items_are_links_to_their_own_pages_on_desktop_and_phone(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $header = substr($html, strpos($html, '<header'), strpos($html, '</header>') - strpos($html, '<header'));
        $mobile = substr($header, strpos($header, 'id="mobile-nav-test"'));
        foreach ([['mega-tours-test', 'tours.index'], ['mega-destinations-test', 'destinations.index'], ['mega-styles-test', 'travel-styles.index'], ['mega-about-test', 'about']] as [$menu, $route]) {
            $this->assertMatchesRegularExpression('~<a href="'.preg_quote(route($route), '~').'"[^>]*aria-controls="'.$menu.'"~', $header, "$route is a link that also opens its menu");
            $this->assertStringContainsString('<a href="'.route($route).'" class="flex min-h-12 flex-1', $mobile, "$route is a link in the phone menu");
            $this->get(route($route))->assertOk();
        }
        $page = $this->get('/tours')->assertOk()->getContent();
        foreach (['self-drive', 'chauffeur-driven', 'coach-tours'] as $slug) {
            $this->assertStringContainsString('href="'.route('tours.show', $slug).'" class="after:absolute', $page);
        }
        $this->get('/sitemap.xml')->assertSee('<loc>'.route('tours.index').'</loc>', false);
    }

    public function test_three_why_travel_with_us_cards_use_the_clients_svg_icons(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        foreach (['local-expertise', 'tailor-made', 'handpicked-stays'] as $name) {
            $this->assertStringContainsString("images/why-icons/$name.svg", $html);
            $this->assertFileExists(public_path("images/why-icons/$name.svg"));
        }
        $this->assertStringContainsString('Routes and stops chosen by people', $html, 'card text is unchanged');
    }

    public function test_the_quiz_bottom_band_has_three_trust_points_and_no_more_thirty_seconds_line(): void
    {
        foreach (['/', '/about'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('Your details stay private', $html, $url);
            $this->assertStringContainsString('Free, no obligation', $html, $url);
            $this->assertStringContainsString('No spam, ever', $html, $url);
            $this->assertStringNotContainsString('Takes less than 30 seconds', $html, $url);
        }
    }

    public function test_the_phone_menu_has_the_reference_layout_and_the_header_enquire_button_is_hidden_on_phones(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('max-sm:!hidden lg:hidden', $html, 'the header Enquire Now is hidden below 640px');
        $nav = substr($html, strpos($html, 'id="mobile-nav-test"'));
        $nav = substr($nav, 0, strpos($nav, '</nav>'));
        $this->assertStringContainsString('aria-controls="m-sub-tours"', $nav);
        $this->assertStringContainsString('aria-controls="m-sub-about"', $nav);
        $this->assertStringContainsString('All packages', $nav);
        $this->assertStringContainsString('Get a free quote', $nav);
        $this->assertStringContainsString('Self Drive', $nav, 'tour type cards');
        $this->assertStringContainsString('Meet Our Team', $nav, 'about cards');
        $this->assertStringContainsString('Contact Us', $nav);
    }

    public function test_below_the_fold_home_sections_are_lazy_for_alpine_and_skip_layout_until_near(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertGreaterThanOrEqual(8, substr_count($html, 'x-ignore data-lazy-alpine class="cv-auto'), 'sections start Alpine only when they scroll into view');
        $this->assertStringContainsString('cv-auto', file_get_contents(base_path('resources/css/app.css')), 'content-visibility helper');
        $this->assertStringContainsString('data-lazy-alpine', file_get_contents(base_path('resources/js/app.js')), 'the lazy start-up code');
    }
}