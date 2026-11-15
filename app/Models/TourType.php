<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TourType extends Model
{
    protected $fillable = ['name', 'slug', 'summary', 'image', 'image_alt', 'sort', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function scopeActive($q)
    {
        return $q->where('active', true)->orderBy('sort');
    }
}
