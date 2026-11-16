<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** A destination (Bruges, Ghent, ...): one accordion panel on the home page and one card on the Destinations page. Visitors only see the word "Destinations". */
class Experience extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'image', 'image_alt', 'image_focus', 'sort', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class);
    }

    public function scopeActive($q)
    {
        return $q->where('active', true)->orderBy('sort');
    }
}
