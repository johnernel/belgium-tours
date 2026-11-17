<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A person on the Meet Our Team page. Placeholders show a neutral avatar until a real photo is uploaded. */
class TeamMember extends Model
{
    protected $fillable = ['name', 'role', 'bio', 'photo', 'photo_alt', 'sort', 'is_placeholder', 'active'];

    protected function casts(): array
    {
        return ['is_placeholder' => 'boolean', 'active' => 'boolean'];
    }

    public function scopeActive($q)
    {
        return $q->where('active', true)->orderBy('sort')->orderBy('id');
    }
}
