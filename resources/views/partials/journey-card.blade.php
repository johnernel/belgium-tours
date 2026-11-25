{{-- THE package card. One markup + one set of styles (the `.pkg-card*` rules in resources/css/app.css) for every package card on the site:
     home carousels, /packages, tour type pages, "You might also like" and the old Ways-to-Travel section (via partials/package-card.blade.php).
     Same vertical layout at every screen size: photo on top with a tour type badge, a meta row (days, nights, route), the title, the summary,
     a "More Info" link, a hairline, then the price and the "Get a Quote" button. Include with: package. Optional: level (heading level, default 3).
     Change the look in app.css, change the content/structure here. Other variables passed by old callers (tag, solid, square) are ignored. --}}
@php
    $days = $package->nights + 1;
    $symbol = ['EUR' => '€', 'USD' => '$', 'GBP' => '£'][$package->currency] ?? $package->currency.' ';
    $highlights = array_slice($package->highlights ?? [], 0, 3);
    $level = $level ?? 3;
    $typeName = $package->tourType?->name;
@endphp
<article class="pkg-card group">
    <div class="pkg-card__media">
        @if($package->hero_image)
            <x-photo :base="$package->hero_image" :alt="$package->hero_image_alt ?: $package->title" sizes="(min-width:1024px) 30vw, (min-width:640px) 44vw, 92vw" fetchpriority="low" class="pkg-card__img size-full" />
        @endif
        @if($typeName)<span class="pkg-card__badge">{{ $typeName }}</span>@endif

        {{-- Hover (and keyboard focus): three highlights marked with tiny orange dashes --}}
        @if($highlights)
            <div class="pkg-card__hover">
                <ul>
                    @foreach($highlights as $h)
                        <li><span aria-hidden="true"></span>{{ $h }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="pkg-card__body">
        <p class="pkg-card__meta">
            <span>{{ $days }} {{ $days === 1 ? 'day' : 'days' }}</span>
            @if($package->nights > 0)<span>{{ $package->nights }} {{ $package->nights === 1 ? 'night' : 'nights' }}</span>@endif
            @if($package->route)<span>{{ $package->route }}</span>@endif
        </p>

        <h{{ $level }} class="pkg-card__title">{{ $package->title }}</h{{ $level }}>

        @if(filled($package->summary))
            <p class="pkg-card__desc">{{ $package->summary }}</p>
        @endif

        <a href="{{ route('packages.show', $package->slug) }}" class="pkg-card__more">More Info<span class="sr-only"> about {{ $package->title }}</span></a>

        <div class="pkg-card__foot">
            @if($package->price_from !== null)
                <div class="pkg-card__price">
                    @if($package->original_price !== null && (float) $package->original_price > (float) $package->price_from)
                        <s class="pkg-card__was" aria-label="Was from {{ $symbol }}{{ number_format((float) $package->original_price) }}">From {{ $symbol }}{{ number_format((float) $package->original_price) }}</s>
                    @else
                        <span class="pkg-card__from">From</span>
                    @endif
                    <span class="pkg-card__amount">{{ $symbol }}{{ number_format((float) $package->price_from) }}</span>
                    <span class="pkg-card__per">pp</span>
                </div>
            @else
                <div class="pkg-card__price pkg-card__price--stack">
                    <span class="pkg-card__amount pkg-card__amount--text">Price on request</span>
                    <span class="pkg-card__per">Tailored to your dates</span>
                </div>
            @endif

            <button type="button" class="pkg-card__cta" @click="$store.modal.open({ packageId: '{{ $package->id }}', packageName: {{ \Illuminate\Support\Js::from($package->title) }}, tourType: {{ \Illuminate\Support\Js::from($package->tourType?->name) }} })">Get a Quote</button>
        </div>
    </div>
</article>
