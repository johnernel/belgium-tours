<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = ['name', 'location', 'quote', 'is_placeholder', 'sort', 'active'];

    protected function casts(): array
    {
        return ['is_placeholder' => 'boolean', 'active' => 'boolean'];
    }

    public function scopeActive($q)
    {
        return $q->where('active', true)->orderBy('sort');
    }
}
