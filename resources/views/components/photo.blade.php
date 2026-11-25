@props([
    'base',            // public path without size suffix, e.g. images/photos/bruges-canal
    'alt' => '',
    'sizes' => '100vw',
    'eager' => false,  // true for the hero image (no lazy loading, high fetch priority)
    'defer' => false,  // true = no request until the page has loaded and is idle (app.js fills in src/srcset from the data-defer-* attributes); for off-screen slideshow photos
    'ratio' => [1600, 1067],
    'widths' => [480, 800, 1280, 1600],
])
@php
    $srcset = collect($widths)->map(fn ($w) => asset($base.'-'.$w.'.webp').' '.$w.'w')->implode(', ');
    $fallback = asset($base.'-'.($widths[1] ?? $widths[0]).'.webp');
@endphp
<img @if($defer) data-defer-src="{{ $fallback }}" data-defer-srcset="{{ $srcset }}" @else src="{{ $fallback }}" srcset="{{ $srcset }}" @endif sizes="{{ $sizes }}"
     width="{{ $ratio[0] }}" height="{{ $ratio[1] }}" alt="{{ $alt }}"
     @if($eager) fetchpriority="high" decoding="async" @else loading="lazy" decoding="async" @endif
     {{ $attributes->merge(['class' => 'object-cover']) }}>
