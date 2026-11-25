<?php

namespace App\Support;

/**
 * UK to US English for visible website text (the site's audience is in the United States).
 *
 * Used by the data migration that converts the content stored in the database, and by the test that keeps the code free of the
 * old spellings. It only swaps whole words and leaves anything that looks like a code word, URL, e-mail address, slug or
 * identifier alone (a word touching a hyphen, underscore, slash, dot-segment, @ or # is never changed), so integration names such as
 * `website-enquiry`, `/enquiry`, `enquiry_submit` or "Enquiry ID" are safe.
 */
class UsEnglish
{
    /** [pattern fragment (case-insensitive, whole word), replacement for the lower-case form] */
    private const MAP = [
        ['enquir(y|ies|ed|ing)', 'inquir$1'],
        ['enquire(s)?', 'inquire$1'],
        ['travell(er|ers|ing|ed)', 'travel$1'],
        ['(organi|personali|customi|speciali|recogni|reali|prioriti|apologi|summari)s(e|ed|es|ing|ation|ations)', '$1z$2'],
        ['colour(s|ed|ful|ing)?', 'color$1'],
        ['favour(ite|ites|s|ed|ing)?', 'favor$1'],
        ['honour(s|ed|ing)?', 'honor$1'],
        ['neighbour(s|hood|hoods)?', 'neighbor$1'],
        ['behaviour(s)?', 'behavior$1'],
        ['flavour(s|ed)?', 'flavor$1'],
        ['centre(s|d)?', 'center$1'],
        ['licence(s)?', 'license$1'],
        ['catalogue(s)?', 'catalog$1'],
        ['hire car(s)?', 'rental car$1'],
        ['car hire', 'car rental'],
        ['autumn', 'fall'],
        ['holiday(s)?', 'vacation$1'],
        ['whilst', 'while'],
        ['amongst', 'among'],
        ['learnt', 'learned'],
        ['cancell(ed|ing)', 'cancel$1'],
        ['fortnight', 'two weeks'],
        ['grey', 'gray'],
        ['programme(s)?', 'program$1'],
        ['jewellery', 'jewelry'],
        ['theatre(s)?', 'theater$1'],
        ['cosy', 'cozy'],
    ];

    /** Characters that mean "this word is part of a code word, URL or address": never change it. */
    private const BEFORE = '(?<![\w\-\/_.@#:])';

    private const AFTER = '(?![\w\-\/_@]|\.\w)';

    public static function convert(string $text): string
    {
        foreach (self::MAP as [$from, $to]) {
            $pattern = '~'.self::BEFORE.'('.$from.')'.self::AFTER.'~iu';
            $text = preg_replace_callback($pattern, function (array $m) use ($from, $to) {
                // Re-run the pattern on the matched word to fill the $1 groups, then copy the capitalisation.
                $new = preg_replace('~^'.$from.'$~iu', $to, $m[1]);
                if ($new === null) {
                    return $m[1];
                }
                if ($m[1] === mb_strtoupper($m[1]) && mb_strlen($m[1]) > 1) {
                    return mb_strtoupper($new);
                }
                if (mb_substr($m[1], 0, 1) === mb_strtoupper(mb_substr($m[1], 0, 1))) {
                    return mb_strtoupper(mb_substr($new, 0, 1)).mb_substr($new, 1);
                }

                return $new;
            }, $text);
        }

        return $text;
    }

    /** Strings, arrays and JSON text (the string leaves only, never keys); anything else comes back untouched. */
    public static function convertValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($v) => self::convertValue($v), $value);
        }
        if (! is_string($value) || $value === '') {
            return $value;
        }
        $trim = ltrim($value);
        if ($trim !== '' && ($trim[0] === '{' || $trim[0] === '[')) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return json_encode(self::convertValue($decoded));
            }
        }

        return self::convert($value);
    }
}
