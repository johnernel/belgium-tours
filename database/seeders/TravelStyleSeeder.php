<?php

namespace Database\Seeders;

use App\Models\Experience;
use App\Models\Package;
use App\Models\TravelStyle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The "Travel Styles" (themes for a Belgium trip) and the intro text of each destination page. Copy is short and general, no invented facts.
 * Packages are linked to a style by matching words in the package title, route and highlights, so the trip counts shown are always real.
 * Safe to run again: a style that already exists keeps its edited text, it only gains any package links it is missing.
 */
class TravelStyleSeeder extends Seeder
{
    /** [name, short line, intro paragraph, photo, alt text, focal point, words that link a package to this style] */
    private function styles(): array
    {
        return [
            ['Medieval Cities', 'Canals, belfries and old market squares.',
                'Belgium has some of Europe\'s best-preserved medieval cities. Walk along canals, past gabled houses and up to the bell towers that rise over the market squares. These trips are built around the old towns, with time to stop, wander and eat well.',
                'bruges-canal', 'A canal lined with brick houses and the Belfry tower in Bruges', '32% 50%', ['Medieval', 'Bruges', 'Ghent', 'Highlights']],
            ['Art & Architecture', 'Art Nouveau streets and grand guildhalls.',
                'From the guildhouses of the Grand Place to Art Nouveau streets and Gothic town halls, Belgian cities are full of buildings worth slowing down for. These trips pick the places where the architecture is the point, with a local guide where you want one.',
                'brussels-grand-place', 'Guildhouses of the Grand Place in Brussels lit at twilight', '50% 50%', ['Art Nouveau', 'Antwerp', 'Highlights']],
            ['Food & Chocolate', 'Chocolate shops and local tastes, at an easy pace.',
                'Chocolate, waffles and regional specialties are part of any Belgium trip. These journeys build tastings and market visits into the day, without a rushed timetable.',
                'bruges-market', 'The Belfry on the Market Square in Bruges at dusk', '50% 50%', ['Chocolate']],
            ['Beer & Abbeys', 'Belgian beer traditions and abbey visits.',
                'Belgium is known for its beer and for the abbeys with a brewing tradition. These private journeys visit breweries and abbeys with someone who knows the way and the stories.',
                'ghent-graslei', 'Visitors beside the canal at Graslei in Ghent', '45% 50%', ['Beer', 'Abbey', 'Abbeys']],
            ['History & Heritage', 'Battlefields, memorials and museums.',
                'Flanders Fields and the towns around Ypres hold a lot of 20th-century history. These trips map out the memorials and museums so you can take them at your own pace, with a guide if you prefer.',
                'ypres-cloth-hall', 'The Cloth Hall in Ypres', '50% 50%', ['Flanders', 'Ypres', 'Fields']],
            ['Nature & Countryside', 'Forested hills, river valleys and quiet villages.',
                'Away from the cities, the Ardennes offer wooded hills, winding rivers and small towns. These trips are for slower days: scenic drives, a riverside stop and a village for the night.',
                'la-roche', 'The Ourthe river and wooded hills at La Roche-en-Ardenne', '50% 50%', ['Ardennes', 'Meuse', 'Dinant', 'Durbuy', 'Namur', 'Countryside']],
        ];
    }

    /** The paragraph on each destination's page (the seeder for destinations only creates the short line). */
    public static function destinationIntros(): array
    {
        return [
            'bruges' => 'Bruges is one of Belgium\'s best-preserved medieval cities, with canals, cobbled lanes and the Belfry on the Market Square. It is easy to explore on foot and works well as a first stop, on its own or combined with Ghent.',
            'ghent' => 'Ghent has a castle, lively canal-side quays at Graslei and the feel of a busy student town. Many trips pair it with Bruges, and it is just as good on its own.',
            'brussels' => 'Brussels is the capital, with the guildhouses of the Grand Place, Art Nouveau streets and plenty of chocolate shops. It suits a day trip, a weekend or the start of a longer journey.',
            'antwerp' => 'Antwerp\'s old town sits below the cathedral spire, with fashion and design close by. It combines well with nearby Mechelen on a relaxed city drive.',
            'leuven' => 'Leuven is a university town with a Gothic town hall and lively squares. It is a natural companion to Brussels on a weekend trip.',
            'ardennes' => 'The Ardennes are forested hills, river valleys and quiet villages. Our trips here visit places such as Namur, Durbuy and Dinant, with time for scenic drives and slow stops.',
        ];
    }

    public function run(): void
    {
        foreach ($this->styles() as $i => [$name, $line, $intro, $photo, $alt, $focus, $words]) {
            $style = TravelStyle::firstOrCreate(['slug' => Str::slug($name)], [
                'name' => $name, 'description' => $line, 'intro' => $intro,
                'image' => 'images/photos/'.$photo, 'image_alt' => $alt, 'image_focus' => $focus, 'sort' => $i + 1, 'active' => true,
            ]);

            // A style that has no trips yet gets them by keyword (a style that already has trips keeps exactly the ones an admin chose).
            if ($style->packages()->doesntExist()) {
                $ids = Package::all()->filter(function ($p) use ($words) {
                    $text = $p->title.' '.$p->route.' '.implode(' ', (array) $p->highlights);

                    return collect($words)->contains(fn ($w) => stripos($text, $w) !== false);
                })->pluck('id');
                $style->packages()->syncWithoutDetaching($ids);
            }
        }

        foreach (self::destinationIntros() as $slug => $intro) {
            Experience::where('slug', $slug)->whereNull('intro')->update(['intro' => $intro]);
        }
    }
}
