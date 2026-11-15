<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Package;
use App\Models\TourType;
use Illuminate\Database\Seeder;

/**
 * SAMPLE DATA ONLY. Names, itineraries and durations are invented starting points;
 * prices are intentionally empty ("Price on request") until the client confirms them.
 * Listed in docs/reference/CONTENT_TO_CONFIRM.md.
 */
class PackageSeeder extends Seeder
{
    public function run(): void
    {
        if (Package::exists()) {
            return;
        }

        $be = Destination::where('slug', 'belgium')->value('id');
        $benl = Destination::where('slug', 'belgium-and-netherlands')->value('id');
        $type = fn ($slug) => TourType::where('slug', $slug)->value('id');

        $rows = [
            ['Belgium Self Drive: Bruges, Ghent and Brussels', 'self-drive', $be, 7, 'A relaxed week by hire car through Belgium\'s best-loved cities, with hotels and routes planned for you.', true,
                ['Medieval Bruges and its canals', 'Ghent\'s castle and waterways', 'Brussels\' Grand-Place', 'Flexible pace with pre-planned routes']],
            ['Flanders Fields and Cities Self Drive', 'self-drive', $be, 8, 'Combine city breaks with the quiet landscapes and history of the Flanders countryside, at your own speed.', false,
                ['Ypres and the Flanders Fields memorials', 'Bruges and Ghent city stays', 'Scenic back roads', 'Free time built in']],
            ['Private Chauffeur: Belgium Highlights', 'chauffeur-driven', $be, 6, 'A private driver-guide takes you between Belgium\'s highlights, with local stories at every stop.', true,
                ['Private driver-guide throughout', 'Brussels, Bruges, Ghent and Antwerp', 'No parking or navigation worries', 'Itinerary adjusts as you go']],
            ['Chauffeur Driven: Belgium and the Netherlands', 'chauffeur-driven', $benl, 9, 'Cross the border in comfort: Belgium\'s medieval cities and the Netherlands\' canals with one private driver.', false,
                ['One driver across two countries', 'Bruges, Antwerp and Amsterdam', 'Door-to-door transfers', 'Private and flexible']],
            ['Belgium by Coach: Cities and Canals', 'coach-tours', $be, 7, 'A small-group guided tour of Belgium\'s main cities with hotels and transport organised.', true,
                ['Guided small-group travel', 'Brussels, Bruges, Ghent and Antwerp', 'No driving', 'Travel with like-minded guests']],
            ['Belgium and Netherlands Coach Tour', 'coach-tours', $benl, 10, 'A longer guided coach journey linking Belgium\'s historic cities with the Netherlands.', false,
                ['Guided throughout', 'Two countries, one itinerary', 'Hotels and transport included', 'Easy, social touring']],
        ];

        $heroes = [
            ['images/photos/bruges-market', 'The Belfry on the Market Square in Bruges at dusk'],
            ['images/photos/countryside', 'A tree-lined country road in Belgium'],
            ['images/photos/brussels-grand-place', 'Guildhouses of the Grand Place in Brussels lit at twilight'],
            ['images/photos/bruges-belfry', 'The Belfry of Bruges rising above the old town'],
            ['images/photos/ghent-graslei', 'Visitors beside the canal at Graslei in Ghent'],
            ['images/photos/gravensteen', 'The medieval Gravensteen castle in Ghent at sunset'],
        ];

        foreach ($rows as $i => [$title, $typeSlug, $dest, $nights, $summary, $featured, $highlights]) {
            Package::create([
                'hero_image' => $heroes[$i][0],
                'hero_image_alt' => $heroes[$i][1],
                'title' => $title,
                'slug' => \Illuminate\Support\Str::slug($title),
                'tour_type_id' => $type($typeSlug),
                'destination_id' => $dest,
                'nights' => $nights,
                'price_from' => null, // [CONFIRM: price]
                'currency' => 'EUR',
                'summary' => $summary,
                'description' => '<p>'.$summary.'</p><p><em>Sample itinerary: [CONFIRM: final itinerary and inclusions]. Every trip can be tailored to your dates, pace and budget.</em></p>',
                'highlights' => $highlights,
                'itinerary' => [
                    ['title' => 'Day 1: Arrival', 'text' => '[SAMPLE] Arrive in Belgium and settle into your first hotel.'],
                    ['title' => 'Day 2 onwards', 'text' => '[SAMPLE] Day-by-day plan to be confirmed for this trip.'],
                ],
                'inclusions' => ['[CONFIRM: inclusions, e.g. hotels, transfers, guide]'],
                'exclusions' => ['[CONFIRM: exclusions, e.g. flights, meals not listed]'],
                'faqs' => [['question' => 'Can this trip be changed?', 'answer' => 'Yes. Tell us what you would like to adjust and we will update the plan and quote.']],
                'is_published' => true,
                'is_featured' => $featured,
                'sort' => $i + 1,
            ]);
        }
    }
}
