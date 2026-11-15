<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['slug', 'title', 'body', 'content', 'seo_title', 'meta_description', 'noindex'];

    protected function casts(): array
    {
        return ['content' => 'array', 'noindex' => 'boolean'];
    }

    /** Editable text blocks (home sections etc.) with a fallback. */
    public function block(string $key, string $default = ''): string
    {
        return (string) (($this->content[$key] ?? null) ?: $default);
    }

    public static function bySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }
}
