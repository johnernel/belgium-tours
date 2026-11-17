<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PackageImage;
use App\Support\PlaceImages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackagePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function package(): Package
    {
        return Package::published()->where('slug', 'bruges-ghent-medieval-escape')->firstOrFail();
    }

    public function test_the_hero_has_the_main_quiz_form_linked_to_the_package_and_the_sidebar_form_is_gone(): void
    {
        $package = $this->package();
        $html = $this->get(route('packages.show', $package->slug))->assertOk()->getContent();

        $this->assertStringContainsString('id="quiz"', $html);
        $this->assertStringContainsString('Belgium, Tailored to You in 30 Seconds', strip_tags($html));
        $this->assertStringContainsString('nav-overlay', $html, 'transparent header over the photo hero');
        $this->assertStringNotContainsString('aria-label="Request a quote"', $html);
        $this->assertStringNotContainsString('Get a quote for this trip', $html);
        $this->assertStringContainsString('name="package_id" value="'.$package->id.'"', $html, 'the quiz enquiry stays linked to the package');
        $this->assertStringContainsString('Ask us a question', $html);
        $this->assertStringContainsString($package->title, strip_tags($html));
    }

    public function test_every_highlight_and_every_day_has_an_image(): void
    {
        $package = $this->package();
        $html = $this->get(route('packages.show', $package->slug))->assertOk()->getContent();

        $hl = substr($html, strpos($html, 'id="hl"'), strpos($html, 'id="ov"') - strpos($html, 'id="hl"'));
        $this->assertSame(count($package->highlights), substr_count($hl, '<img'), 'one image per highlight');

        $it = substr($html, strpos($html, 'id="it"'));
        $it = substr($it, 0, strpos($it, '</section>'));
        $this->assertSame(count($package->itinerary), substr_count($it, '<details name="itinerary"'));
        $this->assertSame(count($package->itinerary) * 2, substr_count($it, '<img'), 'a thumbnail and a larger image per day');
    }

    public function test_place_images_come_from_the_places_named_and_avoid_repeats(): void
    {
        $package = $this->package(); // route "Bruges · Ghent"
        $images = PlaceImages::forPackage($package, ['Canal-side Bruges old town', "Ghent's castle and waterways", 'Hotels and route planned for you']);

        $this->assertStringContainsString('bruges', $images[0]['base']);
        $this->assertStringContainsString('ghent', $images[1]['base']);
        $this->assertCount(3, array_unique(array_column($images, 'base')), 'three different photos');
        $this->assertSame('', $images[0]['alt'], 'place photos are decorative');
    }

    public function test_the_packages_own_gallery_photos_are_used_first_with_their_alt_text(): void
    {
        $package = $this->package();
        PackageImage::create(['package_id' => $package->id, 'path' => 'images/photos/city-leuven', 'alt' => 'Leuven square', 'sort' => 1]);

        $images = PlaceImages::forPackage($package->fresh('images'), ['Canal-side Bruges old town', 'Anything']);

        $this->assertSame('images/photos/city-leuven', $images[0]['base']);
        $this->assertSame('Leuven square', $images[0]['alt']);
    }
}
