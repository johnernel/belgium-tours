<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\QuizOption;
use App\Models\QuizStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The quiz answers use the client's own SVG files (icon value "svg:name" = public/images/quiz-icons/name.svg). */
class QuizSvgIconsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function icon(string $step, string $value): ?string
    {
        return QuizOption::where('quiz_step_id', QuizStep::where('key', $step)->value('id'))->where('value', $value)->value('icon');
    }

    public function test_every_answer_is_matched_to_the_requested_svg(): void
    {
        $expected = [
            ['season', 'spring', 'svg:spring'], ['season', 'summer', 'svg:summer'], ['season', 'autumn', 'svg:autumn'], ['season', 'winter', 'svg:winter'],
            ['travel_style', 'coach', 'svg:coach'], ['travel_style', 'self-drive', 'svg:driving-myself'], ['travel_style', 'chauffeur', 'svg:with-a-guide'], ['travel_style', 'unsure', 'svg:dont-know-yet'],
            ['flights_booked', 'yes', 'svg:yes'], ['flights_booked', 'no', 'svg:no'],
            ['budget', '1000', 'svg:cash-budget'], ['budget', '2000', 'svg:cash-budget'], ['budget', '3000', 'svg:budget'], ['budget', '4000+', 'svg:budget'],
            ['likelihood', '100', 'svg:100'], ['likelihood', '80', 'svg:80'], ['likelihood', '50', 'svg:50'], ['likelihood', 'researching', 'svg:unsure'],
        ];
        foreach ($expected as [$step, $value, $icon]) {
            $this->assertSame($icon, $this->icon($step, $value), "$step / $value");
        }
    }

    public function test_every_svg_icon_in_use_has_its_file(): void
    {
        $icons = QuizOption::where('icon', 'like', 'svg:%')->pluck('icon')->unique();
        $this->assertCount(16, $icons);
        foreach ($icons as $icon) {
            $this->assertFileExists(public_path('images/quiz-icons/'.substr($icon, 4).'.svg'), $icon);
        }
    }

    public function test_the_quiz_renders_the_svg_icons_without_changing_questions_or_answers(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('class="qicon', $html);
        $this->assertStringContainsString('svg:spring', $html);
        $this->assertStringContainsString('What time of year do you want to travel?', $html);
        $this->assertStringContainsString('Unsure, just researching', $html);
    }

    public function test_the_admin_can_pick_the_svg_icons(): void
    {
        $this->actingAs(AdminUser::where('email', 'admin@test.example')->firstOrFail());
        $this->get('/admin/quiz')->assertOk()->assertSee('svg:driving-myself')->assertSee('svg:cash-budget');
    }
}