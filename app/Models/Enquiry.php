<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enquiry extends Model
{
    public const STATUSES = ['new' => 'New', 'contacted' => 'Contacted', 'quoted' => 'Quoted', 'won' => 'Won', 'lost' => 'Lost'];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'tracking_first' => 'array',
            'tracking_last' => 'array',
            'consent_given' => 'boolean',
            'consent_at' => 'datetime',
            'ghl_synced_at' => 'datetime',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
