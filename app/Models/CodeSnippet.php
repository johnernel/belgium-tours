<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One custom code snippet (Admin > Header & Footer). The code is stored and output exactly as entered; only a BARE script or style
 * (code that does not start with a tag) is wrapped in <script> / <style> for convenience.
 */
class CodeSnippet extends Model
{
    public const TYPES = ['script' => 'Script (JavaScript)', 'style' => 'Style (CSS)', 'html' => 'HTML (any code, tracking pixel, widget)'];

    public const PLACEMENTS = [
        'head' => 'In <head>',
        'body_start' => 'Start of <body> (right after the opening tag)',
        'body_end' => 'End of <body> (before the closing tag)',
    ];

    public const SCOPES = ['all' => 'Entire website', 'only' => 'Only on these pages', 'except' => 'Everywhere except these pages'];

    protected $fillable = ['name', 'type', 'placement', 'code', 'scope', 'pages', 'sort', 'is_active'];

    protected function casts(): array
    {
        return ['pages' => 'array', 'is_active' => 'boolean'];
    }

    /** The markup written into the page. */
    public function output(): string
    {
        $code = (string) $this->code;

        if ($this->type === 'html' || preg_match('/^\s*</', $code)) {
            return $code;
        }

        $tag = $this->type === 'style' ? 'style' : 'script';

        return "<$tag>\n$code\n</$tag>";
    }

    /** Normalise a path, slug or full URL typed by the admin to "/path" (lower case, no query, no trailing slash). */
    public static function normalizePath(string $raw): string
    {
        $raw = trim($raw);
        if (preg_match('~^https?://~i', $raw)) {
            $raw = (string) (parse_url($raw, PHP_URL_PATH) ?? '/');
        }
        $raw = preg_replace('/[?#].*$/', '', $raw);
        $raw = '/'.trim((string) $raw, '/');

        return strtolower($raw);
    }

    /** Does this snippet apply to the given request path ("/", "/packages/x")? */
    public function appliesTo(string $path): bool
    {
        if ($this->scope === 'all') {
            return true;
        }

        $path = self::normalizePath($path);
        $hit = false;
        foreach ((array) $this->pages as $pattern) {
            $regex = '~^'.str_replace('\*', '.*', preg_quote(self::normalizePath((string) $pattern), '~')).'$~u';
            if (preg_match($regex, $path)) {
                $hit = true;
                break;
            }
        }

        return $this->scope === 'only' ? $hit : ! $hit;
    }

    /** "Entire website", "Only on 3 pages", ... for the list. */
    public function scopeLabel(): string
    {
        $n = count((array) $this->pages);

        return match ($this->scope) {
            'only' => 'Only on '.$n.' page'.($n === 1 ? '' : 's'),
            'except' => 'All except '.$n.' page'.($n === 1 ? '' : 's'),
            default => 'Entire website',
        };
    }
}