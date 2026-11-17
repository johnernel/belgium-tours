<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $fillable = [
        'title', 'slug', 'tour_type_id', 'destination_id', 'nights', 'price_from', 'currency', 'route',
        'summary', 'description', 'highlights', 'itinerary', 'inclusions', 'exclusions', 'faqs',
        'hero_image', 'hero_image_alt', 'seo_title', 'meta_description', 'social_image',
        'noindex', 'is_published', 'is_featured', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'highlights' => 'array',
            'itinerary' => 'array',
            'inclusions' => 'array',
            'exclusions' => 'array',
            'faqs' => 'array',
            'noindex' => 'boolean',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'price_from' => 'decimal:2',
        ];
    }

    public function tourType(): BelongsTo
    {
        return $this->belongsTo(TourType::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function experiences(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Experience::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PackageImage::class)->orderBy('sort');
    }

    /** Trips whose route or title mentions a place (accent-insensitive on MySQL). Used by the ?place= filter and the home map. */
    public function scopeMentioning($q, string $place)
    {
        $place = trim(preg_replace('/[%_\\\\]/', '', $place));

        return $place === '' ? $q : $q->where(fn ($w) => $w->where('route', 'like', "%{$place}%")->orWhere('title', 'like', "%{$place}%"));
    }

    public function scopePublished($q)
    {
        return $q->where('is_published', true);
    }

    /** "From EUR 1,234" or a visible placeholder when no price has been confirmed. */
    public function priceLabel(): string
    {
        if ($this->price_from === null) {
            return 'Price on request';
        }
        $symbol = ['EUR' => '€', 'USD' => '$', 'GBP' => '£'][$this->currency] ?? $this->currency.' ';

        return 'From '.$symbol.number_format((float) $this->price_from);
    }
}
