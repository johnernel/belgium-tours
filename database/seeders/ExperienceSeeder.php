<?php

namespace Database\Seeders;

use App\Models\Experience;
use App\Models\Package;
use Illuminate\Database\Seeder;

/**
 * The five "top destinations" and which sample journeys belong to each.
 * Descriptions are plain, general copy (not claims about the business): edit in Admin > Top destinations.
 */
class ExperienceSeeder extends Seeder
{
    public function run(): void
    {
        if (Experience::exists()) {
            return;
        }

        // [name, slug, description, photo, alt, package titles]
        $rows = [
            ['Chocolate & Pralines', 'chocolate-pralines',
                'Belgium is famous for its chocolate. Visit chocolatiers, taste pralines still made in small batches and learn how a good one is judged.',
                'experience-chocolate', 'A single praline chocolate on a white cloth',
                ['Brussels Art Nouveau & Chocolate Day', 'Chocolate & Beer Trail']],
            ['Beer & Abbeys', 'beer-abbeys',
                'From abbey breweries to small family brewers, Belgian beer is a culture of its own. Pair tastings with quiet abbey grounds and country lanes.',
                'experience-abbeys', 'The brick tower of an abbey brewery under a blue sky',
                ['Beer & Abbeys Private Journey', 'Chocolate & Beer Trail']],
            ['Art & Architecture', 'art-architecture',
                'Guildhouse squares, Gothic belfries and Art Nouveau townhouses. Belgium\'s cities are open-air galleries, best seen unhurried.',
                'brussels-grand-place', 'Guildhouses of the Grand Place in Brussels lit at twilight',
                ['Brussels Art Nouveau & Chocolate Day', 'Bruges, Ghent & Antwerp Grand Tour', 'Antwerp & Mechelen City Drive', 'Belgium Highlights in 7 Days']],
            ['Flanders Fields History', 'flanders-fields-history',
                'A respectful look at the Western Front around Ypres: memorials, museums and cemeteries, with time to reflect and a guide who knows the stories.',
                'experience-flanders', 'A 1917 photograph of soldiers on a duckboard track through the shattered trees of Chateau Wood near Ypres',
                ['Flanders Fields Heritage Drive', 'Flanders Fields Private Guided Day', 'Flanders Fields Remembrance Tour']],
            ['Canals & Cycling', 'canals-cycling',
                'Flat landscapes, towpaths and canal-side towns make Belgium easy to explore slowly, by boat, on foot or by bicycle.',
                'bruges-canal', 'A canal lined with brick houses and the Belfry tower in Bruges',
                ['Bruges & Ghent Medieval Escape', 'Cities & Canals Small-Group Tour', 'Bruges, Ghent & Antwerp Grand Tour']],
        ];

        foreach ($rows as $i => [$name, $slug, $description, $photo, $alt, $titles]) {
            $experience = Experience::create([
                'name' => $name, 'slug' => $slug, 'description' => $description,
                'image' => 'images/photos/'.$photo, 'image_alt' => $alt, 'sort' => $i + 1,
            ]);
            $experience->packages()->sync(Package::whereIn('title', $titles)->pluck('id'));
        }
    }
}
