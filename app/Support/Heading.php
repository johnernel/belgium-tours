<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Two-colour headlines: the main part in the heading colour, an accent part in italic.
 *
 *   Heading::html('Three Ways to *Travel* Belgium')  -> accent is the starred part
 *   Heading::html('Every journey, personally crafted.') -> no stars: the last word is the accent
 *
 * Every headline is also put in Capitalized Case ("Three Ways to Travel Belgium"): each word starts with a
 * capital except short joining words (a, and, of, to, ...) that sit in the middle. A single word is left plain.
 * Output is escaped. Colours come from the .heading-accent CSS (slate on light backgrounds, amber inside an
 * .on-dark heading).
 */
class Heading
{
    /** Short words kept in lower case unless they start the headline, end it or follow a full stop or colon. */
    private const SMALL_WORDS = ['a', 'an', 'and', 'as', 'at', 'but', 'by', 'for', 'from', 'in', 'nor', 'of', 'on', 'or', 'per', 'the', 'to', 'up', 'via', 'vs', 'with'];

    public static function html(?string $text): HtmlString
    {
        $text = self::titleCase(trim((string) $text));

        if (preg_match('/^(.*?)\*(.+?)\*(.*)$/s', $text, $m)) {
            return new HtmlString(e($m[1]).'<em class="heading-accent">'.e($m[2]).'</em>'.e($m[3]));
        }

        $cut = strrpos($text, ' ');
        if ($cut === false) {
            return new HtmlString(e($text));
        }

        return new HtmlString(e(substr($text, 0, $cut)).' <em class="heading-accent">'.e(substr($text, $cut + 1)).'</em>');
    }

    /** Capitalized Case for a headline. Keeps *asterisks*, punctuation and words that are already capitals (USA). */
    public static function titleCase(string $text): string
    {
        $parts = preg_split('/(\s+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $wordIdx = array_keys(array_filter($parts, fn ($p) => trim($p) !== ''));
        if (! $wordIdx) {
            return $text;
        }
        $last = end($wordIdx);
        $startsSentence = true;

        foreach ($wordIdx as $i) {
            if (! preg_match('/^([^\p{L}\p{N}]*)(.*?)([^\p{L}\p{N}]*)$/su', $parts[$i], $m) || $m[2] === '') {
                continue;
            }
            [, $pre, $core, $post] = $m;

            if (! $startsSentence && $i !== $last && in_array(mb_strtolower($core), self::SMALL_WORDS, true)) {
                $core = mb_strtolower($core);
            } else {
                $core = mb_strtoupper(mb_substr($core, 0, 1)).mb_substr($core, 1);
                // Self-drive -> Self-Drive
                $core = preg_replace_callback('/(?<=-)\p{Ll}/u', fn ($x) => mb_strtoupper($x[0]), $core);
            }

            $parts[$i] = $pre.$core.$post;
            $startsSentence = (bool) preg_match('/[.!?:]/u', $post);
        }

        return implode('', $parts);
    }
}
