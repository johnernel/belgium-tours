<?php

namespace App\Support;

use App\Models\Package;

/**
 * Picks an image for each Highlight and each Day of a package page.
 *
 *  1. The package's own gallery photos (uploaded in Admin > Packages) are used first, in order, with their own alt text.
 *  2. Otherwise a photo of a place the text names (Bruges, Ghent, ...) from the licensed set in public/images/photos.
 *  3. Otherwise a photo of a place on the package's route or title, cycling through them.
 *  4. Otherwise the package's hero photo.
 *
 * Place photos only show the place, never a claim about the item, so they are decorative (empty alt text).
 */
class PlaceImages
{
    /** keyword pattern => photos of that place (base path without size suffix). */
    private const PLACES = [
        '/bruges|brugge|belfry/i' => ['images/photos/bruges-canal', 'images/photos/bruges-belfry', 'images/photos/bruges-market'],
        '/ghent|gent\b|graslei|gravensteen/i' => ['images/photos/ghent-graslei', 'images/photos/gravensteen'],
        '/brussels|grand[- ]place|atomium/i' => ['images/photos/brussels-grand-place', 'images/photos/flower-carpet'],
        '/antwerp/i' => ['images/photos/city-antwerp'],
        '/leuven|louvain/i' => ['images/photos/city-leuven'],
        '/ardennes|dinant|namur|durbuy|la roche|meuse|bouillon|valley|forest/i' => ['images/photos/la-roche'],
        '/ypres|flanders|passchendaele|countryside|back-road|back road/i' => ['images/photos/countryside'],
    ];

    /**
     * @param  array<int, string>  $texts  one text per item (a highlight, or a day's title and text)
     * @return array<int, array{base: string, alt: string}|null>  same order and length as $texts
     */
    public static function forPackage(Package $package, array $texts): array
    {
        $gallery = $package->images->map(fn ($i) => ['base' => $i->path, 'alt' => (string) $i->alt])->values()->all();
        $routePool = self::routePool($package);
        $uses = []; // how many times each photo is already shown, so a free photo is preferred to a repeat
        $pick = function (array $candidates) use (&$uses) {
            usort($candidates, fn ($a, $b) => ($uses[$a] ?? 0) <=> ($uses[$b] ?? 0)); // stable: keeps the given order on ties
            $uses[$candidates[0]] = ($uses[$candidates[0]] ?? 0) + 1;

            return $candidates[0];
        };
        $out = [];

        foreach (array_values($texts) as $i => $text) {
            if ($gallery) {
                $out[] = $gallery[$i % count($gallery)];

                continue;
            }
            $photos = self::match((string) $text);
            if ($photos) {
                $out[] = ['base' => $pick($photos), 'alt' => ''];

                continue;
            }
            if ($routePool) {
                $out[] = ['base' => $pick($routePool), 'alt' => ''];

                continue;
            }
            $out[] = $package->hero_image ? ['base' => $package->hero_image, 'alt' => ''] : null;
        }

        return $out;
    }

    /** Photos of the places named in a text (first matching place only, so one item shows one place). */
    private static function match(string $text): array
    {
        foreach (self::PLACES as $pattern => $photos) {
            if (preg_match($pattern, $text)) {
                return $photos;
            }
        }

        return [];
    }

    /** Photos of the places on the package's route and title: the first photo of each place, then the second of each, and so on. */
    private static function routePool(Package $package): array
    {
        $haystack = $package->route.' '.$package->title;
        $places = [];
        foreach (self::PLACES as $pattern => $photos) {
            if (preg_match($pattern, $haystack, $m, PREG_OFFSET_CAPTURE)) {
                $places[$m[0][1]] = $photos;
            }
        }
        ksort($places);

        $pool = [];
        $most = $places ? max(array_map('count', $places)) : 0;
        for ($n = 0; $n < $most; $n++) {
            foreach ($places as $photos) {
                if (isset($photos[$n])) {
                    $pool[] = $photos[$n];
                }
            }
        }

        return $pool;
    }
}
