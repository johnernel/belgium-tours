<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A Journal (blog) article. Shown on the home page, /journal and /journal/{slug}. */
class Post extends Model
{
    protected $fillable = ['title', 'slug', 'category', 'excerpt', 'body', 'image', 'image_alt', 'read_minutes', 'published_at', 'is_placeholder', 'active'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'is_placeholder' => 'boolean', 'active' => 'boolean'];
    }

    /** Visible to visitors: switched on and not scheduled for the future. Newest first. */
    public function scopePublished($q)
    {
        return $q->where('active', true)
            ->where(fn ($w) => $w->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->orderByDesc('published_at')->orderByDesc('id');
    }
}
