<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Package;
use App\Models\TourType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * SAMPLE DATA ONLY. Names, routes, highlights and durations are invented starting points;
 * prices are intentionally empty ("Price on request") until the client confirms them.
 * Five journeys per tour type feed the "Three Ways to Travel Belgium" carousel.
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

        // [title, tour type, destination, days, route, photo, photo alt, summary, three highlights]
        $rows = [
            // Self-Drive
            ['Bruges & Ghent Medieval Escape', 'self-drive', $be, 4, 'Bruges · Ghent', 'bruges-canal', 'A canal lined with brick houses and the Belfry tower in Bruges',
                'A relaxed few days by rental car between two of Belgium\'s best-preserved medieval cities.',
                ['Canal-side Bruges old town', 'Ghent\'s castle and waterways', 'Hotels and route planned for you']],
            ['Flanders Fields Heritage Drive', 'self-drive', $be, 3, 'Ypres · Passchendaele', 'countryside', 'A tree-lined country road through Flemish farmland',
                'Drive the quiet roads of the Flanders battlefields at your own pace, with memorials and museums mapped out.',
                ['Ypres and its memorials', 'Quiet back-road driving', 'Stops mapped out for you']],
            ['Ardennes & Meuse Valley Road Trip', 'self-drive', $be, 5, 'Namur · Durbuy · Dinant', 'la-roche', 'The Ourthe river and wooded hills in the Belgian Ardennes',
                'Winding river roads, forested hills and small towns in the Ardennes and along the Meuse.',
                ['River valleys and forest roads', 'Small-town stays', 'Scenic route planned for you']],
            ['Antwerp & Mechelen City Drive', 'self-drive', $be, 3, 'Antwerp · Mechelen', 'bruges-belfry', 'A belfry tower rising above a Belgian old town',
                'Two historic Flemish cities an easy drive apart, with parking and stays arranged.',
                ['Historic city centers', 'Easy short drives', 'Parking and hotels arranged']],
            ['Brussels & Leuven Weekend Drive', 'self-drive', $be, 3, 'Brussels · Leuven', 'flower-carpet', 'A flower carpet in front of the Brussels Grand Place',
                'A short drive-yourself break combining the capital with a lively university town.',
                ['Brussels Grand-Place', 'Leuven\'s old town hall square', 'Flexible, self-guided days']],

            // Chauffeur
            ['Brussels Art Nouveau & Chocolate Day', 'chauffeur-driven', $be, 1, 'Brussels', 'brussels-grand-place', 'Guildhouses of the Grand Place in Brussels lit at twilight',
                'A private day around Brussels with a driver-guide: Art Nouveau streets and a chocolate stop or two.',
                ['Private driver-guide', 'Art Nouveau architecture', 'Chocolate tasting stop']],
            ['Bruges, Ghent & Antwerp Grand Tour', 'chauffeur-driven', $be, 6, 'Bruges · Ghent · Antwerp', 'ghent-graslei', 'Visitors beside the canal at Graslei in Ghent',
                'Belgium\'s three great Flemish cities with one private driver-guide throughout.',
                ['One driver for the whole trip', 'Three historic cities', 'Flexible daily pace']],
            ['Beer & Abbeys Private Journey', 'chauffeur-driven', $be, 3, 'Leuven · Namur', 'gravensteen', 'The medieval Gravensteen castle in Ghent at sunset',
                'Brewery visits and abbey country, with a driver so everyone can enjoy the tasting.',
                ['Brewery and abbey visits', 'Driver means no designated driver', 'Tailored to your tastes']],
            ['Private Ardennes Day Out', 'chauffeur-driven', $be, 2, 'Dinant · La Roche', 'la-roche', 'The Ourthe river and wooded hills at La Roche-en-Ardenne',
                'Riverside towns and forested hills with a private driver-guide.',
                ['River and cliff scenery', 'Local lunch stop', 'Private and flexible']],
            ['Flanders Fields Private Guided Day', 'chauffeur-driven', $be, 1, 'Ypres · Passchendaele', 'countryside', 'A tree-lined country road in Flanders',
                'A quiet, respectful day at the Flanders memorials with a private guide.',
                ['Private guide and driver', 'Memorials and museums', 'Unhurried pace']],

            // Coach Tour
            ['Belgium Highlights in 7 Days', 'coach-tours', $be, 7, 'Brussels · Bruges · Ghent · Antwerp', 'ghent-graslei', 'Visitors beside the canal at Graslei in Ghent',
                'A guided small-group tour of Belgium\'s main cities with hotels and transport organized.',
                ['Guided small-group travel', 'Four great cities', 'No driving, no planning']],
            ['Chocolate & Beer Trail', 'coach-tours', $be, 5, 'Brussels · Bruges · Leuven', 'flower-carpet', 'A flower carpet in front of the Brussels Grand Place',
                'Belgium\'s two famous exports, with tastings along a guided coach route.',
                ['Chocolate and beer tastings', 'Guided throughout', 'Easy group travel']],
            ['Flanders Fields Remembrance Tour', 'coach-tours', $be, 4, 'Ypres · Passchendaele', 'countryside', 'A tree-lined country road in Flanders',
                'A guided coach tour of the Western Front memorials, museums and cemeteries.',
                ['Expert guiding', 'Key memorial sites', 'Transport and hotels included']],
            ['Cities & Canals Small-Group Tour', 'coach-tours', $be, 6, 'Bruges · Ghent · Antwerp', 'bruges-market', 'The Belfry on the Market Square in Bruges at dusk',
                'Canal cities and market squares with a guide and a small group.',
                ['Small-group guiding', 'Canal-side stays', 'No driving']],
            ['Belgium & Netherlands Coach Tour', 'coach-tours', $benl, 10, 'Brussels · Antwerp · Amsterdam', 'bruges-belfry', 'A belfry tower rising above a Belgian old town',
                'A longer guided journey linking Belgium\'s historic cities with the Netherlands.',
                ['Two countries, one tour', 'Hotels and transport included', 'Easy social touring']],
        ];

        $first = [];
        foreach ($rows as $i => [$title, $typeSlug, $dest, $days, $route, $photo, $alt, $summary, $highlights]) {
            Package::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'tour_type_id' => $type($typeSlug),
                'destination_id' => $dest,
                'nights' => max(0, $days - 1),
                'price_from' => null, // [CONFIRM: price]
                'currency' => 'EUR',
                'route' => $route,
                'summary' => $summary,
                'description' => '<p>'.e($summary).'</p><p><em>Sample itinerary: [CONFIRM: final itinerary and inclusions]. Every trip can be tailored to your dates, pace and budget.</em></p>',
                'highlights' => $highlights,
                'itinerary' => [
                    ['title' => 'Day 1: Arrival', 'text' => '[SAMPLE] Arrive in Belgium and settle into your first hotel.'],
                    ['title' => 'Day 2 onwards', 'text' => '[SAMPLE] Day-by-day plan to be confirmed for this trip.'],
                ],
                'inclusions' => ['[CONFIRM: inclusions, e.g. hotels, transfers, guide]'],
                'exclusions' => ['[CONFIRM: exclusions, e.g. flights, meals not listed]'],
                'faqs' => [['question' => 'Can this trip be changed?', 'answer' => 'Yes. Tell us what you would like to adjust and we will update the plan and quote.']],
                'hero_image' => 'images/photos/'.$photo,
                'hero_image_alt' => $alt,
                'is_published' => true,
                'is_featured' => ! isset($first[$typeSlug]) && ($first[$typeSlug] = true),
                'sort' => $i + 1,
            ]);
        }
    }
}
