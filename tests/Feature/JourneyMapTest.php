<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Support\JourneyMap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JourneyMapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_home_has_the_journey_section_between_the_faq_and_the_blog(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $faq = strpos($html, 'aria-labelledby="faq-title"');
        $journey = strpos($html, 'aria-labelledby="journey-title"');
        $blog = strpos($html, 'aria-labelledby="blog-title"');
        $this->assertNotFalse($journey);
        $this->assertGreaterThan($faq, $journey);
        $this->assertLessThan($blog, $journey);

        $text = strip_tags($html);
        $this->assertStringContainsString('The Journey', $text);
        $this->assertStringContainsString('Never Ends', $text);
        $this->assertStringContainsString('every journey adds a new chapter to your story', $text);
        $this->assertStringContainsString('href="'.route('destinations.index').'"', $html);
        $this->assertStringContainsString('Explore Destinations', $text);
    }

    public function test_the_map_has_ten_cities_nine_route_legs_planes_and_five_polaroids_with_three_or_more_photos(): void
    {
        $data = JourneyMap::data();

        $this->assertSame(['Bruges', 'Ghent', 'Brussels', 'Antwerp', 'Leuven', 'Liège', 'Namur', 'Dinant', 'Ypres', 'Mechelen'], array_column($data['cities'], 'name'));
        $this->assertCount(9, $data['routes']);
        $this->assertCount(3, $data['planes']);
        $this->assertCount(11, $data['provinces'], 'all Belgian provinces incl. Brussels-Capital');

        $polaroids = array_filter($data['cities'], fn ($c) => $c['polaroid']);
        $this->assertCount(5, $polaroids);
        foreach ($polaroids as $c) {
            $this->assertGreaterThanOrEqual(3, count($c['images']), $c['name'].' needs at least three photos to cycle');
            $this->assertCount(count($c['images']), array_unique(array_column($c['images'], 'src')), $c['name'].' photos are different');
        }

        $html = $this->get('/')->getContent();
        $this->assertSame(10, substr_count($html, 'class="jm-dot"'));
        $this->assertSame(5, substr_count($html, 'class="jm-pol"'));
        $this->assertSame(9, substr_count($html, 'class="jm-route"'));
        $this->assertSame(3, substr_count($html, '<animateMotion'));
    }

    public function test_every_photo_file_exists_and_is_credited(): void
    {
        $credited = array_column(config('credits.photos'), 'slug');
        foreach (JourneyMap::data()['cities'] as $c) {
            foreach ($c['images'] as $img) {
                $slug = basename(parse_url($img['src'], PHP_URL_PATH), '-800.webp');
                foreach ([480, 800, 1280] as $w) {
                    $this->assertFileExists(public_path("images/photos/{$slug}-{$w}.webp"));
                }
                $this->assertContains($slug, $credited, "$slug must be listed in config/credits.php");
                $this->assertNotSame('', trim($img['alt']));
            }
        }
    }

    public function test_the_count_on_each_city_matches_the_trips_its_link_shows(): void
    {
        foreach (JourneyMap::data()['cities'] as $c) {
            $page = $this->get($c['url'])->assertOk()->getContent();
            $this->assertSame($c['count'], substr_count($page, 'class="journey-card'), "{$c['name']}: count and filtered list agree");
            $this->assertStringContainsString('Showing trips that visit', $page);
        }
    }

    public function test_the_place_filter_finds_published_trips_only_and_ignores_wildcards(): void
    {
        $type = \App\Models\TourType::first();
        Package::create(['title' => 'Secret Dinant weekend', 'slug' => 'secret-dinant', 'tour_type_id' => $type->id, 'nights' => 2, 'is_published' => false]);

        $before = Package::published()->mentioning('Dinant')->count();
        $this->assertSame($before, substr_count($this->get('/packages?place=Dinant')->getContent(), 'class="journey-card'));
        $this->get('/packages?place=Dinant')->assertDontSee('Secret Dinant weekend');
        $this->assertSame(Package::published()->count(), Package::published()->mentioning('%')->count(), 'a bare wildcard is stripped, so it filters nothing');
        $this->get('/packages?place='.str_repeat('x', 200))->assertOk();
    }

    public function test_the_section_copy_is_editable_and_present_in_the_home_page_content(): void
    {
        $content = \App\Models\Page::bySlug('home')->content;

        foreach (['journey_title_1', 'journey_title_2', 'journey_text', 'journey_cta'] as $key) {
            $this->assertNotEmpty($content[$key] ?? null, "$key is seeded");
        }
    }
}
