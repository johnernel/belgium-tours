<?php

namespace Tests\Feature;

use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_sitemap_lists_public_pages_and_packages(): void
    {
        $package = Package::firstOrFail();

        $res = $this->get('/sitemap.xml')->assertOk();
        $this->assertStringContainsString('application/xml', $res->headers->get('Content-Type'));
        $res->assertSee(route('home'), false)->assertSee(route('packages.show', $package->slug), false);
        $res->assertDontSee('thank-you', false)->assertDontSee('/admin', false);
    }

    public function test_robots_blocks_admin_and_thank_you(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin')->assertSee('Disallow: /thank-you');
    }

    public function test_every_public_page_renders_with_one_h1_and_a_canonical(): void
    {
        $package = Package::firstOrFail();
        foreach (['/', '/packages', '/packages/'.$package->slug, '/about', '/contact', '/terms-and-conditions', '/privacy-policy', '/thank-you'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertSame(1, substr_count($html, '<h1'), "$url should have exactly one h1");
            $this->assertStringContainsString('<link rel="canonical"', $html, $url);
            $this->assertStringContainsString('<meta name="description"', $html, $url);
        }
    }

    public function test_thank_you_is_noindex_and_fires_the_conversion_event(): void
    {
        $this->get('/thank-you')->assertOk()->assertSee('noindex', false)->assertSee('enquiry_thank_you', false);
    }

    public function test_home_hero_is_a_three_slide_slideshow_with_a_trust_marquee_and_no_dropdown(): void
    {
        $html = $this->get('/homepage-test')->assertOk()->getContent();

        $this->assertSame(3, substr_count($html, 'hero-slide hero-slide-'), 'three slideshow images');
        $this->assertStringContainsString('marquee-track', $html);
        $this->assertStringNotContainsString('Way to see your destinations', $html);
        $this->assertStringNotContainsString('hero-way', $html);
    }

    public function test_first_load_does_not_request_below_the_fold_decoration_or_the_other_hero_photos(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, 'data-defer-srcset='), 'hero photos 2 and 3 are filled in by app.js after the page has loaded');
        $this->assertSame(1, preg_match_all('~<img\s+src="[^"]*hero-[a-z-]+-800\.webp~', $html), 'only the first hero photo has a real src');
        $this->assertStringNotContainsString('style="background-image', $html, 'decorative backgrounds are set by Alpine when their section wakes up');
        $this->assertStringNotContainsString(' poster="', $html, 'the video poster is bound lazily');
        $this->assertStringContainsString(":poster=\"'", $html);
        $this->assertStringContainsString('bodoni-moda-latin-wght-normal.woff2', $html, 'the weight-only Bodoni file is preloaded');
    }

    public function test_the_tailor_made_section_shows_its_copy_and_the_cta_style(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        foreach (['Tailor-made in Belgium', 'Every Journey, Personally', 'Bespoke itineraries', 'Handpicked stays', 'Local expert guides', 'Plan My Journey'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringContainsString('bg-cta text-white hover:bg-cta-hover', $html, 'CTA button style: white text on the 4.5:1 orange, dark green hover');
    }

    public function test_the_ways_to_travel_section_has_video_tabs_and_journey_cards(): void
    {
        $html = $this->get('/homepage-test')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'data-lazy-video'), 'only one video-background section');
        $this->assertStringContainsString('media/ways-loop.webm', $html);
        $this->assertStringContainsString('media/ways-poster.webp', $html);
        $this->assertStringContainsString('Signature Journeys', $html);
        $this->assertStringContainsString('<em class="heading-accent">Travel</em>', $html);
        foreach (['Self-Drive', 'Chauffeur', 'Coach Tour'] as $tab) {
            $this->assertMatchesRegularExpression('/role="tab"[^>]*>[\s\S]{0,1500}'.preg_quote($tab, '/').'/', $html);
        }
        $this->assertSame(3, substr_count($html, 'role="tabpanel" aria-labelledby="tab-'));
        $this->assertSame(15, substr_count($html, 'pkg-card group'), 'five journeys per tab');
        foreach (['Bruges & Ghent Medieval Escape', 'Brussels Art Nouveau & Chocolate Day', 'Belgium Highlights in 7 Days'] as $title) {
            $this->assertStringContainsString(e($title), $html);
        }
        $this->assertStringContainsString('Get a Quote', $html);
        $this->assertStringContainsString('More Info', $html);
        $this->assertStringContainsString('Bruges · Ghent', $html, 'route line');
    }

    public function test_only_the_home_page_has_a_background_video(): void
    {
        foreach (['/packages', '/about', '/contact', '/tours/self-drive'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('data-lazy-video', false);
        }
    }

    public function test_every_section_headline_on_home_is_two_coloured(): void
    {
        $html = $this->get('/homepage-test')->assertOk()->getContent();

        foreach (['why-title', 'ways-title', 'how-title', 't-title', 'faq-title'] as $id) {
            $this->assertMatchesRegularExpression('/id="'.$id.'"[^>]*>[^<]*(<[^>]+>[^<]*)*<em class="heading-accent">/s', $html, "$id should have an accent part");
        }
    }

    public function test_home_hero_has_the_main_quiz_form_beside_centred_text_and_keeps_the_marquee(): void
    {
        $html = $this->get('/homepage-test')->assertOk()->getContent();
        $hero = substr($html, strpos($html, '<section class="relative isolate -mt-[4.5rem]'), 14000);

        $this->assertStringContainsString('id="quiz"', $hero);
        $this->assertStringContainsString('Belgium, Tailored to You in 30 Seconds', strip_tags($hero));
        $this->assertStringContainsString('lg:grid-cols-[1.1fr_0.9fr]', $hero, 'same two-column layout as the other pages');
        $this->assertStringContainsString('items-center', $hero);
        $this->assertStringContainsString('lg:text-[4.5rem]', $hero, 'same headline size as the other pages');
        $this->assertStringContainsString('class="marquee', $html, 'the trust marquee stays in the hero');
        $this->assertStringContainsString('hero-slide-3', $html, 'the photo slideshow is kept');
    }

    public function test_home_has_faq_structured_data(): void
    {
        $this->get('/homepage-test')->assertOk()->assertSee('"@type":"FAQPage"', false);
    }

    public function test_unknown_page_returns_custom_404(): void
    {
        $this->get('/does-not-exist')->assertNotFound()->assertSee('We Took a Wrong', false)->assertSee('<em class="heading-accent">Turn</em>', false);
    }

    public function test_gtm_snippet_only_appears_when_configured(): void
    {
        $this->get('/')->assertDontSee('googletagmanager.com');

        \App\Models\Setting::put('gtm_id', 'GTM-TEST123');
        $this->get('/')->assertSee('GTM-TEST123', false);
    }

    public function test_a_page_with_no_meta_description_falls_back_to_the_site_default(): void
    {
        $level = ob_get_level();
        \App\Models\Setting::put('default_meta_description', 'Site wide fallback description');

        foreach (['home', 'about', 'contact', 'terms', 'privacy'] as $slug) {
            \App\Models\Page::bySlug($slug)->update(['meta_description' => null, 'seo_title' => null]);
        }

        foreach (['/', '/about', '/contact', '/terms-and-conditions', '/privacy-policy'] as $url) {
            $this->get($url)->assertOk()->assertSee('<meta name="description" content="Site wide fallback description">', false);
        }
        $this->assertSame($level, ob_get_level(), 'an empty @section must not leave a buffer open');
    }

    public function test_security_headers_are_sent(): void
    {
        $this->get('/')->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }
}
