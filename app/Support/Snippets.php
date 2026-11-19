<?php

namespace App\Support;

use App\Models\CodeSnippet;
use Illuminate\Support\HtmlString;

/** Builds the custom code for one placement on the current page (Admin > Header & Footer). Never breaks the page if the table is missing. */
class Snippets
{
    public static function render(string $placement, ?string $path = null): HtmlString
    {
        $path ??= '/'.ltrim((string) request()->path(), '/');

        if (str_starts_with(ltrim($path, '/'), 'admin')) {
            return new HtmlString('');
        }

        $out = [];
        foreach (self::active() as $snippet) {
            if ($snippet->placement === $placement && $snippet->appliesTo($path)) {
                $out[] = $snippet->output();
            }
        }

        return new HtmlString($out ? "\n".implode("\n", $out)."\n" : '');
    }

    /** Active snippets in output order, loaded once per request (kept on the request, so nothing leaks between requests or tests). */
    private static function active(): array
    {
        $attributes = request()->attributes;

        if (! $attributes->has('code_snippets')) {
            try {
                $attributes->set('code_snippets', CodeSnippet::where('is_active', true)->orderBy('sort')->orderBy('id')->get()->all());
            } catch (\Throwable $e) {
                $attributes->set('code_snippets', []); // table not created yet: the site keeps working
            }
        }

        return $attributes->get('code_snippets');
    }
}