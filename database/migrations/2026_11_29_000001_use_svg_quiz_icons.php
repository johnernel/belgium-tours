<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The client's own SVG icons for the main quiz answers (season, travel style, flights, budget, likelihood). An icon value
 * "svg:name" means the file public/images/quiz-icons/name.svg. The previous icon names are kept here so down() can restore them.
 */
return new class extends Migration
{
    /** step key, option value, previous icon, new icon */
    private array $changes = [
        ['season', 'spring', 'flower', 'svg:spring'], ['season', 'summer', 'sun', 'svg:summer'],
        ['season', 'autumn', 'leaf', 'svg:autumn'], ['season', 'winter', 'snowflake', 'svg:winter'],
        ['travel_style', 'coach', 'bus', 'svg:coach'], ['travel_style', 'self-drive', 'car', 'svg:driving-myself'],
        ['travel_style', 'chauffeur', 'user', 'svg:with-a-guide'], ['travel_style', 'unsure', 'help', 'svg:dont-know-yet'],
        ['flights_booked', 'yes', 'plane', 'svg:yes'], ['flights_booked', 'no', 'plane-off', 'svg:no'],
        ['budget', '1000', 'coins', 'svg:cash-budget'], ['budget', '2000', 'wallet', 'svg:cash-budget'],
        ['budget', '3000', 'banknote', 'svg:budget'], ['budget', '4000+', 'gem', 'svg:budget'],
        ['likelihood', '100', 'circle-check', 'svg:100'], ['likelihood', '80', 'smile', 'svg:80'],
        ['likelihood', '50', 'meh', 'svg:50'], ['likelihood', 'researching', 'search', 'svg:unsure'],
    ];

    public function up(): void
    {
        foreach ($this->changes as [$step, $value, , $to]) {
            $this->set($step, $value, $to);
        }
    }

    public function down(): void
    {
        foreach ($this->changes as [$step, $value, $from]) {
            $this->set($step, $value, $from);
        }
    }

    private function set(string $step, string $value, string $icon): void
    {
        $stepId = DB::table('quiz_steps')->where('key', $step)->value('id');
        if ($stepId) {
            DB::table('quiz_options')->where('quiz_step_id', $stepId)->where('value', $value)->update(['icon' => $icon]);
        }
    }
};