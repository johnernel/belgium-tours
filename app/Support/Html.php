<?php

namespace App\Support;

/** Light sanitiser for admin-entered rich text (Trix output). Admins are trusted, this is defence in depth. */
class Html
{
    private const ALLOWED = '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4><a><blockquote><div><span><figure><figcaption><pre>';

    public static function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $html = strip_tags($html, self::ALLOWED);
        $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
        $html = preg_replace('/(href|src)\s*=\s*(["\'])\s*javascript:[^"\']*\2/i', '$1="#"', $html);

        return $html;
    }
}
