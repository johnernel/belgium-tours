<?php

namespace Database\Seeders;

use App\Models\QuizOption;
use App\Models\QuizStep;
use Illuminate\Database\Seeder;

/** Default 9-step hero quiz (step 9, the contact form, is built into the component). */
class QuizSeeder extends Seeder
{
    public function run(): void
    {
        if (QuizStep::exists()) {
            return;
        }

        $steps = [
            ['destination', 'Would you like to visit Belgium or Europe?', 'tiles', [
                ['Belgium', 'belgium', 'map-pin'], ['Europe', 'europe', 'globe'],
            ]],
            ['season', 'What time of year do you want to travel?', 'tiles', [
                ['Spring', 'spring', 'svg:spring'], ['Summer', 'summer', 'svg:summer'],
                ['Fall', 'autumn', 'svg:autumn'], ['Winter', 'winter', 'svg:winter'],
            ]],
            ['travel_style', 'What is your favorite way to see a new country?', 'tiles', [
                ['By coach with other travelers', 'coach', 'svg:coach'], ['Driving myself', 'self-drive', 'svg:driving-myself'],
                ['With a guide driving', 'chauffeur', 'svg:with-a-guide'], ["Don't know yet", 'unsure', 'svg:dont-know-yet'],
            ]],
            ['group_size', 'How many people are in your group?', 'select', array_map(
                fn ($n) => [$n === 10 ? '10 or more' : (string) $n, (string) $n, null], range(1, 10))],
            ['nights', 'How many nights?', 'select', array_map(
                fn ($n) => [$n === 14 ? '14 or more' : "$n nights", (string) $n, null], range(3, 14))],
            ['flights_booked', 'Have you booked flights?', 'tiles', [
                ['Yes', 'yes', 'svg:yes'], ['No', 'no', 'svg:no'],
            ]],
            ['budget', 'Do you have a budget in mind per person?', 'tiles', [
                ['€1,000', '1000', 'svg:cash-budget'], ['€2,000', '2000', 'svg:cash-budget'],
                ['€3,000', '3000', 'svg:budget'], ['€4,000 or more', '4000+', 'svg:budget'],
            ]],
            ['likelihood', 'How likely are you to travel?', 'tiles', [
                ["I'm 100% sure I'm going", '100', 'svg:100'], ["I'm 80% sure", '80', 'svg:80'],
                ['50/50', '50', 'svg:50'], ['Unsure, just researching', 'researching', 'svg:unsure'],
            ]],
        ];

        foreach ($steps as $i => [$key, $question, $type, $options]) {
            $step = QuizStep::create(['key' => $key, 'question' => $question, 'type' => $type, 'sort' => $i + 1]);
            foreach ($options as $j => [$label, $value, $icon]) {
                QuizOption::create([
                    'quiz_step_id' => $step->id, 'label' => $label, 'value' => $value, 'icon' => $icon, 'sort' => $j + 1,
                ]);
            }
        }
    }
}
