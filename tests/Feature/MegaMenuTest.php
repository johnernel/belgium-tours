<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\TourType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MegaMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function menu(): string
    {
        $html = $this->get('/packages')->assertOk()->getContent();
        $start = strpos($html, 'id="mega-tours-test"');
        $this->assertNotFalse($start, 'the mega menu is in the header');

        return substr($html, $start, strpos($html, 'id="mobile-nav-test"', $start) - $start);
    }

    public function test_tour_types_menu_has_a_column_per_tour_type_with_a_one_line_blurb_image_and_two_featured_trips(): void
    {
        $menu = $this->menu();

        $this->assertSame(TourType::active()->count(), substr_count($menu, '<section '));
        foreach (TourType::active()->get() as $type) {
            $this->assertStringContainsString('href="'.route('tours.show', $type->slug).'"', $menu);
            $this->assertNotEmpty($type->menu_text);
            $this->assertStringContainsString($type->menu_text, $menu);
            $this->assertLessThanOrEqual(40, mb_strlen($type->menu_text), 'the blurb has to fit on one line');
            $this->assertStringContainsString('href="'.route('packages.index', ['type' => $type->slug]).'"', $menu);
        }
        // 2 featured trips under each tour type
        $this->assertSame(TourType::active()->count() * 2, substr_count($menu, 'class="group/row flex'));
    }

    public function test_featured_packages_come_first_and_unpublished_ones_never_show(): void
    {
        $type = TourType::where('slug', 'self-drive')->firstOrFail();
        $hidden = Package::create(['title' => 'Hidden sample trip', 'slug' => 'hidden-sample-trip', 'tour_type_id' => $type->id, 'nights' => 4, 'is_published' => false, 'is_featured' => true]);
        $star = Package::create(['title' => 'Star sample trip', 'slug' => 'star-sample-trip', 'tour_type_id' => $type->id, 'nights' => 5, 'is_published' => true, 'is_featured' => true, 'sort' => 0]);

        $menu = $this->menu();

        $this->assertStringContainsString('Star sample trip', $menu);
        $this->assertStringNotContainsString($hidden->title, $menu);
    }
}
