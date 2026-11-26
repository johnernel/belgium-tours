<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** A travel style (a theme for a Belgium trip: medieval cities, art and architecture, ...): one tile in the Travel Styles menu and one page of its own. */
class TravelStyle extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'intro', 'image', 'image_alt', 'image_focus', 'seo_title', 'meta_description', 'sort', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_travel_style');
    }

    public function scopeActive($q)
    {
        return $q->where('active', true)->orderBy('sort');
    }
}