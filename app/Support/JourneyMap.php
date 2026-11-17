<?php

namespace App\Support;

use App\Models\Package;

/**
 * Data for the home page "The Journey Never Ends" section: the Belgium outline (generated from Natural Earth public
 * domain boundaries, see resources/data/belgium-map.json), the ten cities with their photos and tour counts, the
 * curved route that links them and the small planes that fly along it.
 *
 * Everything on screen is computed here, so nothing is typed twice: a city's tour count is the number of published
 * packages whose route or title mentions it, and the "View tours" link opens the Packages page filtered the same way.
 */
class JourneyMap
{
    /** Order of the journey: each city connects to the next one. */
    private const ROUTE = ['Ypres', 'Bruges', 'Ghent', 'Antwerp', 'Mechelen', 'Brussels', 'Leuven', 'Liège', 'Namur', 'Dinant'];

    /** Route legs (0 = first) that carry a plane, with the seconds a lap takes and the start delay. */
    private const PLANES = [[0, 7, 0], [2, 9, 3], [6, 11, 6]];

    /**
     * name => [slug, polaroid [x%, y% of the map, rotation deg, parallax depth px] or null, label side, photos [[base, alt]]]
     * The first photo of a city with a polaroid is also its first slide in the detail card.
     */
    private const CITIES = [
        'Bruges' => ['bruges', [13, 10, -6, 10], [
            ['images/photos/bruges-canal', 'Brick houses beside a canal in Bruges'],
            ['images/photos/bruges-belfry', 'The Belfry tower rising above the rooftops of Bruges'],
            ['images/photos/bruges-market', 'The Belfry on the Markt square in Bruges at dusk'],
        ]],
        'Ghent' => ['ghent', [30, 44, 4, 14], [
            ['images/photos/ghent-graslei', 'Visitors relaxing beside the canal at Graslei in Ghent'],
            ['images/photos/gravensteen', 'The medieval Gravensteen castle in Ghent at sunset'],
            ['images/photos/ghent-evening', 'The Graslei guild houses reflected in the canal in the evening light'],
        ]],
        'Brussels' => ['brussels', [47, 66, -4, 18], [
            ['images/photos/brussels-grand-place', 'The Grand Place in Brussels at twilight'],
            ['images/photos/flower-carpet', 'A flower carpet laid out in front of the Brussels Grand Place'],
            ['images/photos/brussels-facades', 'Gilded guild house facades on the Grand-Place in Brussels'],
        ]],
        'Antwerp' => ['antwerp', [72, 9, 5, 12], [
            ['images/photos/city-antwerp', 'The clock of Antwerp cathedral'],
            ['images/photos/antwerp-tower', 'The cathedral tower rising above the Grote Markt in Antwerp'],
            ['images/photos/antwerp-guildhouses', 'Guild houses on the Grote Markt in Antwerp'],
        ]],
        'Leuven' => ['leuven', [88, 29, -5, 16], [
            ['images/photos/city-leuven', 'The Grote Markt in Leuven'],
            ['images/photos/leuven-hall-tower', 'The ornate Gothic town hall of Leuven'],
            ['images/photos/leuven-hall-church', 'Leuven town hall beside St Peter\'s church'],
        ]],
        'Liège' => ['liege', null, [['images/photos/liege-guillemins', 'The curved white roof of Liège-Guillemins station']]],
        'Namur' => ['namur', null, [['images/photos/namur-citadel', 'The citadel of Namur above the Sambre and the Meuse']]],
        'Dinant' => ['dinant', null, [['images/photos/dinant-citadel', 'The citadel and collegiate church above the Meuse in Dinant']]],
        'Ypres' => ['ypres', null, [['images/photos/ypres-cloth-hall', 'The Cloth Hall on the Grote Markt in Ypres']]],
        'Mechelen' => ['mechelen', null, [['images/photos/mechelen-tower', 'The tower of St Rumbold\'s Cathedral rising above Mechelen']]],
    ];

    public static function data(): array
    {
        $map = json_decode((string) file_get_contents(resource_path('data/belgium-map.json')), true);
        [$w, $h] = $map['viewBox'];

        $cities = [];
        foreach (self::CITIES as $name => [$slug, $polaroid, $photos]) {
            [$px, $py] = $map['cities'][$name];
            $count = Package::published()->mentioning($name)->count();
            $cities[$name] = [
                'slug' => $slug, 'name' => $name,
                'x' => round($px / $w * 100, 2), 'y' => round($py / $h * 100, 2), 'px' => $px, 'py' => $py,
                'count' => $count,
                'countText' => $count === 0 ? 'Tailor-made trips on request' : $count.($count === 1 ? ' tour' : ' tours'),
                'url' => route('packages.index', ['place' => $name]),
                'polaroid' => $polaroid ? ['x' => $polaroid[0], 'y' => $polaroid[1], 'rot' => $polaroid[2], 'depth' => $polaroid[3]] : null,
                'images' => array_map(fn ($p) => [
                    'src' => asset($p[0].'-800.webp'),
                    'srcset' => asset($p[0].'-480.webp').' 480w, '.asset($p[0].'-800.webp').' 800w, '.asset($p[0].'-1280.webp').' 1280w',
                    'alt' => $p[1],
                ], $photos),
            ];
        }

        // Curved dotted legs between consecutive cities; the bend alternates side so the route reads like a flight path.
        $routes = [];
        for ($i = 0; $i < count(self::ROUTE) - 1; $i++) {
            $a = $cities[self::ROUTE[$i]];
            $b = $cities[self::ROUTE[$i + 1]];
            $dx = $b['px'] - $a['px'];
            $dy = $b['py'] - $a['py'];
            $len = sqrt($dx * $dx + $dy * $dy);
            $bend = $len * 0.28 * ($i % 2 === 0 ? 1 : -1);
            $cx = ($a['px'] + $b['px']) / 2 - $dy / max($len, 1) * $bend;
            $cy = ($a['py'] + $b['py']) / 2 + $dx / max($len, 1) * $bend;
            $routes[] = [
                'i' => $i, 'from' => $a['slug'], 'to' => $b['slug'],
                'd' => sprintf('M%.1f %.1fQ%.1f %.1f %.1f %.1f', $a['px'], $a['py'], $cx, $cy, $b['px'], $b['py']),
            ];
        }

        return [
            'viewBox' => [$w, $h],
            'provinces' => $map['provinces'],
            'cities' => array_values($cities),
            'routes' => $routes,
            'planes' => array_map(fn ($p) => ['route' => $p[0], 'dur' => $p[1], 'begin' => $p[2]], self::PLANES),
        ];
    }
}
