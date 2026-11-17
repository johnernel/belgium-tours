<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Nicer, distinct icons for the quiz answers (flights, budget, likelihood). An icon is only replaced when it still has
 * the old starter value, so anything edited in Admin > Quiz is left alone.
 */
return new class extends Migration
{
    /** step key, option value, old starter icon, new icon */
    private array $changes = [
        ['flights_booked', 'yes', 'check', 'plane'],
        ['flights_booked', 'no', 'x', 'plane-off'],
        ['budget', '1000', 'wallet', 'coins'],
        ['budget', '3000', 'wallet', 'banknote'],
        ['budget', '4000+', 'wallet', 'gem'],
        ['likelihood', '100', 'check', 'circle-check'],
        ['likelihood', '80', 'check', 'smile'],
        ['likelihood', '50', 'help', 'meh'],
        ['likelihood', 'researching', 'help', 'search'],
    ];

    public function up(): void
    {
        $this->apply(fn ($c) => [$c[2], $c[3]]);
    }

    public function down(): void
    {
        $this->apply(fn ($c) => [$c[3], $c[2]]);
    }

    private function apply(callable $fromTo): void
    {
        foreach ($this->changes as $c) {
            [$from, $to] = $fromTo($c);
            $stepId = DB::table('quiz_steps')->where('key', $c[0])->value('id');
            if (! $stepId) {
                continue;
            }
            DB::table('quiz_options')->where('quiz_step_id', $stepId)->where('value', $c[1])->where('icon', $from)->update(['icon' => $to]);
        }
    }
};
