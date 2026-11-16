<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Two-colour headlines: the main part in the heading colour, an accent part in italic.
 *
 *   Heading::html('Three Ways to *Travel* Belgium')  -> accent is the starred part
 *   Heading::html('Every journey, personally crafted.') -> no stars: the last word is the accent
 *
 * A single word is left plain. Output is escaped. Colours come from the .heading-accent CSS
 * (slate on light backgrounds, amber inside an .on-dark heading).
 */
class Heading
{
    public static function html(?string $text): HtmlString
    {
        $text = trim((string) $text);

        if (preg_match('/^(.*?)\*(.+?)\*(.*)$/s', $text, $m)) {
            return new HtmlString(e($m[1]).'<em class="heading-accent">'.e($m[2]).'</em>'.e($m[3]));
        }

        $cut = strrpos($text, ' ');
        if ($cut === false) {
            return new HtmlString(e($text));
        }

        return new HtmlString(e(substr($text, 0, $cut)).' <em class="heading-accent">'.e(substr($text, $cut + 1)).'</em>');
    }
}
