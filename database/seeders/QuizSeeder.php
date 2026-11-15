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
                ['Spring', 'spring', 'flower'], ['Summer', 'summer', 'sun'],
                ['Autumn', 'autumn', 'leaf'], ['Winter', 'winter', 'snowflake'],
            ]],
            ['travel_style', 'What is your favourite way to see a new country?', 'tiles', [
                ['By coach with other travellers', 'coach', 'bus'], ['Driving myself', 'self-drive', 'car'],
                ['With a guide driving', 'chauffeur', 'user'], ["Don't know yet", 'unsure', 'help'],
            ]],
            ['group_size', 'How many people are in your group?', 'select', array_map(
                fn ($n) => [$n === 10 ? '10 or more' : (string) $n, (string) $n, null], range(1, 10))],
            ['nights', 'How many nights?', 'select', array_map(
                fn ($n) => [$n === 14 ? '14 or more' : "$n nights", (string) $n, null], range(3, 14))],
            ['flights_booked', 'Have you booked flights?', 'tiles', [
                ['Yes', 'yes', 'check'], ['No', 'no', 'x'],
            ]],
            ['budget', 'Do you have a budget in mind per person?', 'tiles', [
                ['€1,000', '1000', 'wallet'], ['€2,000', '2000', 'wallet'],
                ['€3,000', '3000', 'wallet'], ['€4,000 or more', '4000+', 'wallet'],
            ]],
            ['likelihood', 'How likely are you to travel?', 'tiles', [
                ["I'm 100% sure I'm going", '100', 'check'], ["I'm 80% sure", '80', 'check'],
                ['50/50', '50', 'help'], ['Unsure, just researching', 'researching', 'help'],
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
