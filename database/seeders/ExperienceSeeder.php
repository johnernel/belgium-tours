<?php

namespace Database\Seeders;

use App\Models\Experience;
use App\Models\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The six "Destinations" (cities and regions) shown as the home page accordion and on /destinations.
 * (Internally the model is called Experience; visitors only ever see "Destinations".)
 * Descriptions are short, general facts about each place. Tours are linked automatically by matching the
 * place name against each package's title and route, so the tour counts shown are always real.
 */
class ExperienceSeeder extends Seeder
{
    public function run(): void
    {
        if (Experience::exists()) {
            return;
        }

        // [name, short description, photo, alt text, focal point, words that link a package to this place]
        $rows = [
            ['Bruges', 'Canals, cobbled lanes and a skyline of gables and bell towers.',
                'bruges-canal', 'A canal lined with brick houses and the Belfry tower in Bruges', '32% 50%', ['Bruges']],
            ['Ghent', 'A castle, lively canal-side quays and a busy student-town feel.',
                'ghent-graslei', 'Visitors beside the canal at Graslei in Ghent', '45% 50%', ['Ghent']],
            ['Brussels', 'Grand squares, Art Nouveau streets and chocolate shops.',
                'brussels-grand-place', 'Guildhouses of the Grand Place in Brussels lit at twilight', '50% 50%', ['Brussels']],
            ['Antwerp', 'A cathedral spire above the old town, with fashion and design close by.',
                'city-antwerp', 'The gold clock faces on the tower of Antwerp Cathedral', '56% 30%', ['Antwerp']],
            ['Leuven', 'A university town with a Gothic town hall and lively squares.',
                'city-leuven', 'The Grote Markt in Leuven beside the Gothic town hall', '8% 50%', ['Leuven']],
            ['Ardennes', 'Forested hills, river valleys and quiet villages.',
                'la-roche', 'The Ourthe river and wooded hills at La Roche-en-Ardenne', '50% 50%', ['Ardennes', 'Dinant', 'La Roche', 'Durbuy', 'Namur']],
        ];

        foreach ($rows as $i => [$name, $description, $photo, $alt, $focus, $words]) {
            $experience = Experience::create([
                'name' => $name, 'slug' => Str::slug($name), 'description' => $description,
                'image' => 'images/photos/'.$photo, 'image_alt' => $alt, 'image_focus' => $focus, 'sort' => $i + 1,
            ]);

            $ids = Package::all()->filter(function ($p) use ($words) {
                $text = $p->title.' '.$p->route;
                foreach ($words as $w) {
                    if (stripos($text, $w) !== false) {
                        return true;
                    }
                }

                return false;
            })->pluck('id');

            $experience->packages()->sync($ids);
        }
    }
}
