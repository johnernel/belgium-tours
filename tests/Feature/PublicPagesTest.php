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

    public function test_home_has_faq_structured_data(): void
    {
        $this->get('/')->assertOk()->assertSee('"@type":"FAQPage"', false);
    }

    public function test_unknown_page_returns_custom_404(): void
    {
        $this->get('/does-not-exist')->assertNotFound()->assertSee('wrong turn');
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
