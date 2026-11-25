@extends('layouts.app')

@section('title', $package->seo_title ?: $package->title.' | '.$site->brand)
@section('description', $package->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $package->summary), 155))
@section('hero', '1')
@if($package->noindex)@section('noindex', '1')@endif
@if($package->social_image)@section('og_image', asset($package->social_image))@elseif($package->hero_image)@section('og_image', asset($package->hero_image.'-1600.webp'))@endif

@push('schema')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Packages', 'item' => route('packages.index')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $package->title, 'item' => route('packages.show', $package->slug)],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    <script type="application/ld+json">{!! json_encode(array_filter([
        '@context' => 'https://schema.org', '@type' => 'TouristTrip',
        'name' => $package->title,
        'description' => strip_tags((string) $package->summary),
        'image' => $package->hero_image ? asset($package->hero_image.'-1600.webp') : null,
        'touristType' => $package->tourType?->name,
        'provider' => ['@type' => 'TravelAgency', 'name' => $site->brand],
        'offers' => $package->price_from ? ['@type' => 'Offer', 'price' => (string) $package->price_from, 'priceCurrency' => $package->currency, 'availability' => 'https://schema.org/InStock'] : null,
    ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @if($package->faqs)
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org', '@type' => 'FAQPage',
        'mainEntity' => collect($package->faqs)->map(fn ($f) => ['@type' => 'Question', 'name' => $f['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['answer']]])->values(),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endif
@endpush

@section('content')
{{-- Same photo hero as the other pages: headline and trip facts on the left, the main quiz form on the right. The enquiry stays linked to this package. --}}
@push('hero-extra')
    <ul class="hero-rise hero-rise-2 mt-6 flex flex-wrap gap-2 text-sm font-bold text-ink">
        <li class="rounded-full bg-amber px-3 py-1.5">{{ $package->nights > 0 ? $package->nights.' nights' : '1 day' }}</li>
        @if($package->tourType)<li class="rounded-full bg-sky px-3 py-1.5">{{ $package->tourType->name }}</li>@endif
        @if($package->destination)<li class="rounded-full bg-cream px-3 py-1.5">{{ $package->destination->name }}</li>@endif
    </ul>
    <div class="hero-rise hero-rise-2 mt-6">
        <p><span class="font-serif text-3xl text-amber">{{ $package->priceLabel() }}</span> <span class="block text-sm text-white/85">per person, based on the trip as described</span></p>
    </div>
@endpush
@include('partials.quiz-hero', [
    'heroTitle' => $package->title,
    'heroTitleClass' => 'max-w-[20ch] text-[2rem] leading-[1.05] sm:text-5xl lg:max-w-[18ch] lg:text-6xl',
    'heroText' => (string) $package->summary,
    'heroImage' => $package->hero_image ?: 'images/photos/bruges-market',
    'heroAlt' => $package->hero_image ? ($package->hero_image_alt ?: $package->title) : '',
    'crumb' => $package->title,
    'crumbMid' => [route('packages.index'), 'Packages'],
])

@php
    // Highlights are one line each. "Title: short description" splits into a header and a panel text; a plain line is the title.
    $hlItems = collect($highlights)->map(function ($h, $i) use ($highlightImages) {
        $h = trim((string) $h);
        $parts = preg_split('/\s*[:\x{2013}\x{2014}]\s+/u', $h, 2);
        $hasText = count($parts) === 2 && mb_strlen($parts[0]) <= 70;

        return [
            'title' => $hasText ? $parts[0] : $h,
            'text' => $hasText ? $parts[1] : 'Ask us about this when you request your quote and we will tailor it to you.',
            'img' => $highlightImages[$i] ?? null,
        ];
    })->all();
    $dayItems = collect($days)->map(fn ($d, $i) => [
        'title' => $d['title'] ?? 'Day '.($i + 1),
        'text' => $d['text'] ?? '',
        'img' => $dayImages[$i] ?? null,
        'points' => array_values(array_filter((array) ($d['points'] ?? []))),
    ])->all();
    $amount = $package->price_from !== null ? substr($package->priceLabel(), 5) : null;
    $duration = $package->nights > 0 ? $package->nights.' nights' : '1 day';
    $facts = array_values(array_filter([
        ['icon' => 'calendar', 'label' => 'Duration', 'value' => $duration],
        $package->tourType ? ['icon' => 'compass', 'label' => 'Tour type', 'value' => $package->tourType->name] : null,
        $package->route ? ['icon' => 'map-pin', 'label' => 'Route', 'value' => $package->route] : null,
        $package->destination ? ['icon' => 'globe', 'label' => 'Destination', 'value' => $package->destination->name] : null,
    ]));
    $quoteJs = \Illuminate\Support\Js::from(['packageId' => (string) $package->id, 'packageName' => $package->title, 'tourType' => $package->tourType?->name]);
@endphp

<div class="bg-white"><div class="container-page py-8 lg:py-10">
    <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_21rem] lg:items-start xl:grid-cols-[minmax(0,1fr)_24rem] xl:gap-14">
        {{-- Main column: every section has the same top and bottom padding --}}
        <div class="min-w-0">
            @if($hlItems)
                <section aria-labelledby="hl" class="py-8 lg:py-10">
                    <p class="mb-3 flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.22em] text-ink"><span class="h-px w-10 bg-amber"></span>Experiences</p><h2 id="hl" class="text-heading">@heading('Tour highlights')</h2>
                    <div class="mt-6">@include('partials.package-accordion', ['items' => $hlItems, 'kind' => 'highlight'])</div>
                </section>
            @endif

            @if($package->description || $facts)
                <section aria-labelledby="ov" class="py-8 lg:py-10">
                    <p class="mb-3 flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.22em] text-ink"><span class="h-px w-10 bg-amber"></span>The trip</p><h2 id="ov" class="text-heading">@heading('Overview')</h2>
                    @if($package->description)<div class="prose-page mt-4 max-w-3xl">{!! $package->description !!}</div>@endif
                    <ul class="mt-6 grid gap-4 sm:grid-cols-2">
                        @foreach($facts as $f)
                            <li class="flex items-center gap-4 rounded-2xl border border-ink/10 bg-white p-4">
                                <span class="grid size-12 shrink-0 place-items-center rounded-full bg-amber/25 text-amber-ink" aria-hidden="true">@icon($f['icon'], 'size-6')</span>
                                <div class="min-w-0"><span class="block text-xs font-semibold uppercase tracking-[0.18em] text-slate">{{ $f['label'] }}</span><span class="block font-display text-lg leading-snug text-ink">{{ $f['value'] }}</span></div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if($dayItems)
                <section aria-labelledby="it" class="py-8 lg:py-10">
                    <p class="mb-3 flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.22em] text-ink"><span class="h-px w-10 bg-amber"></span>Itinerary</p><h2 id="it" class="text-heading">@heading('Day by day')</h2>
                    <div class="mt-6">@include('partials.package-accordion', ['items' => $dayItems, 'kind' => 'day'])</div>
                </section>
            @endif

            @if($package->inclusions || $package->exclusions)
                <section class="grid gap-6 py-8 sm:grid-cols-2 lg:py-10">
                    @if($package->inclusions)
                        <div class="rounded-3xl border border-ink/10 bg-cream p-6">
                            <h2 class="font-display text-2xl">Included</h2>
                            <ul class="mt-4 space-y-3">@foreach($package->inclusions as $i)<li class="flex items-start gap-3"><span class="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full bg-aqua text-ink">@icon('check', 'size-3.5')</span><span>{{ $i }}</span></li>@endforeach</ul>
                        </div>
                    @endif
                    @if($package->exclusions)
                        <div class="rounded-3xl bg-ink/5 p-6">
                            <h2 class="font-display text-2xl">@heading('Not included')</h2>
                            <ul class="mt-4 space-y-3">@foreach($package->exclusions as $i)<li class="flex items-start gap-3"><span class="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full bg-red-100 text-red-700">@icon('x', 'size-3.5')</span><span>{{ $i }}</span></li>@endforeach</ul>
                        </div>
                    @endif
                </section>
            @endif

            @if($package->images->isNotEmpty())
                <section aria-labelledby="gal" class="py-8 lg:py-10"><p class="mb-3 flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.22em] text-ink"><span class="h-px w-10 bg-amber"></span>Gallery</p><h2 id="gal" class="text-heading">@heading('Gallery')</h2>
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach($package->images as $img)
                            <x-photo :base="$img->path" :alt="$img->alt" sizes="(min-width:640px) 22vw, 46vw" class="aspect-[4/3] w-full rounded-2xl" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if($package->faqs)
                <section aria-labelledby="pf" class="py-8 lg:py-10"><p class="mb-3 flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.22em] text-ink"><span class="h-px w-10 bg-amber"></span>Good to know</p><h2 id="pf" class="text-heading">@heading('Questions about this trip')</h2>
                    <div class="mt-4 space-y-3">@foreach($package->faqs as $f)<x-faq-item :question="$f['question']">{{ $f['answer'] }}</x-faq-item>@endforeach</div>
                </section>
            @endif
        </div>

        {{-- Sticky booking card --}}
        <aside class="lg:sticky lg:top-28" aria-label="Book this trip">
            <div class="rounded-[20px] bg-ink p-6 text-cream shadow-card sm:p-7">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cream/70">{{ $amount ? 'From' : 'Price' }}</p>
                @if($amount)
                    <p class="mt-1"><span class="font-display text-4xl text-amber">{{ $amount }}</span> <span class="text-sm text-cream/80">per person</span></p>
                @else
                    <p class="mt-1 font-display text-3xl text-amber">Price on request</p>
                @endif
                <ul class="mt-5 space-y-3 border-t border-cream/15 pt-5 text-sm">
                    <li class="flex items-center gap-3"><span class="grid size-8 shrink-0 place-items-center rounded-full bg-amber/20 text-amber">@icon('calendar', 'size-4')</span>{{ $duration }}</li>
                    @if($package->tourType)<li class="flex items-center gap-3"><span class="grid size-8 shrink-0 place-items-center rounded-full bg-amber/20 text-amber">@icon('compass', 'size-4')</span>{{ $package->tourType->name }}</li>@endif
                    @if($package->destination)<li class="flex items-center gap-3"><span class="grid size-8 shrink-0 place-items-center rounded-full bg-amber/20 text-amber">@icon('map-pin', 'size-4')</span>{{ $package->destination->name }}</li>@endif
                </ul>
                <div class="mt-6 grid gap-3">
                    <x-button variant="primary" size="md" class="w-full" @click="$store.modal.open({{ $quoteJs }})">Get a Quote</x-button>
                    <x-button :href="route('packages.index')" variant="light" size="md" class="w-full">See Other Packages</x-button>
                </div>
                <p class="mt-4 text-center text-xs text-cream/70">Free to request. No payment needed.</p>
            </div>
        </aside>
    </div>
</div></div>

@if($related->isNotEmpty())
    {{-- Top space = bottom space: the bottom is this section's pb-8 (2rem) plus the footer's mt-24 (6rem) = 8rem, so the top is pt-32 (8rem) --}}
    <section class="pb-8 pt-32"><div class="container-page"><h2 class="text-heading">@heading('You might also like')</h2>
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">@foreach($related as $package)@include('partials.package-card', ['package' => $package])@endforeach</div>
    </div></section>
@endif
@endsection
