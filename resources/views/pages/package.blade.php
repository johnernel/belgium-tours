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
    'heroTitleClass' => 'max-w-[20ch] text-4xl leading-[1.05] sm:text-5xl lg:max-w-[18ch] lg:text-6xl',
    'heroText' => (string) $package->summary,
    'heroImage' => $package->hero_image ?: 'images/photos/bruges-market',
    'heroAlt' => $package->hero_image ? ($package->hero_image_alt ?: $package->title) : '',
    'crumb' => $package->title,
    'crumbMid' => [route('packages.index'), 'Packages'],
])

<div class="container-page space-y-14 py-12 lg:space-y-16 lg:py-16">
        @if($highlights)
            <section aria-labelledby="hl"><h2 id="hl" class="text-heading">@heading('Highlights')</h2>
                <ul class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($highlights as $i => $h)
                        @php $img = $highlightImages[$i] ?? null; @endphp
                        <li class="group flex flex-col overflow-hidden rounded-3xl bg-paper shadow-card">
                            @if($img)
                                <div class="relative aspect-[4/3] overflow-hidden bg-sky-soft">
                                    <x-photo :base="$img['base']" :alt="$img['alt']" sizes="(min-width:1024px) 30vw, (min-width:640px) 45vw, 92vw" fetchpriority="low" :ratio="[1600, 1200]" class="absolute inset-0 size-full transition-transform duration-500 group-hover:scale-105" />
                                </div>
                            @endif
                            <p class="flex items-start gap-3 p-5 text-lg font-semibold leading-snug">@icon('check', 'mt-1 size-5 shrink-0 text-amber-deep') <span>{{ $h }}</span></p>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if($package->description)
            <section aria-labelledby="ov"><h2 id="ov" class="text-heading">@heading('Overview')</h2>
                <div class="prose-page mt-4 max-w-3xl">{!! $package->description !!}</div>
            </section>
        @endif

        @if($days)
            <section aria-labelledby="it"><h2 id="it" class="text-heading">@heading('Day by day')</h2>
                <div class="mt-6 space-y-3">
                    @foreach($days as $i => $day)
                        @php $img = $dayImages[$i] ?? null; @endphp
                        <details name="itinerary" @if($loop->first) open @endif class="group overflow-hidden rounded-3xl bg-paper shadow-card">
                            <summary class="flex cursor-pointer list-none items-center gap-4 p-3 pr-5 [&::-webkit-details-marker]:hidden">
                                @if($img)
                                    <span class="relative size-16 shrink-0 overflow-hidden rounded-2xl bg-sky-soft sm:size-[4.5rem]">
                                        <x-photo :base="$img['base']" alt="" :widths="[480, 800]" sizes="72px" :ratio="[480, 480]" class="absolute inset-0 size-full" />
                                    </span>
                                @endif
                                <span class="flex-1 font-display text-lg leading-snug sm:text-xl">{{ $day['title'] ?? 'Day '.($i + 1) }}</span>
                                @icon('chevron-down', 'size-5 shrink-0 text-slate transition-transform group-open:rotate-180')
                            </summary>
                            <div class="grid gap-5 p-5 pt-2 md:grid-cols-[minmax(0,20rem)_1fr] md:items-start">
                                @if($img)
                                    <div class="relative aspect-[4/3] overflow-hidden rounded-2xl bg-sky-soft">
                                        <x-photo :base="$img['base']" :alt="$img['alt']" :widths="[480, 800, 1280]" sizes="(min-width:768px) 320px, 92vw" fetchpriority="low" :ratio="[1600, 1200]" class="absolute inset-0 size-full" />
                                    </div>
                                @endif
                                <p class="text-lg leading-relaxed text-slate">{{ $day['text'] ?? '' }}</p>
                            </div>
                        </details>
                    @endforeach
                </div>
            </section>
        @endif

        @if($package->inclusions || $package->exclusions)
            <section class="grid gap-6 sm:grid-cols-2">
                @if($package->inclusions)
                    <div class="rounded-2xl bg-sky-soft p-6"><h2 class="text-xl">Included</h2><ul class="mt-3 space-y-2">@foreach($package->inclusions as $i)<li class="flex gap-2">@icon('check', 'mt-1 size-4 shrink-0'){{ $i }}</li>@endforeach</ul></div>
                @endif
                @if($package->exclusions)
                    <div class="rounded-2xl bg-cream-deep p-6"><h2 class="text-xl">@heading('Not included')</h2><ul class="mt-3 space-y-2">@foreach($package->exclusions as $i)<li class="flex gap-2">@icon('x', 'mt-1 size-4 shrink-0'){{ $i }}</li>@endforeach</ul></div>
                @endif
            </section>
        @endif

        @if($package->images->isNotEmpty())
            <section aria-labelledby="gal"><h2 id="gal" class="text-heading">@heading('Gallery')</h2>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach($package->images as $img)
                        <x-photo :base="$img->path" :alt="$img->alt" sizes="(min-width:640px) 30vw, 46vw" class="aspect-[4/3] w-full rounded-2xl" />
                    @endforeach
                </div>
            </section>
        @endif

        @if($package->faqs)
            <section aria-labelledby="pf"><h2 id="pf" class="text-heading">@heading('Questions about this trip')</h2>
                <div class="mt-4 space-y-3">@foreach($package->faqs as $f)<x-faq-item :question="$f['question']">{{ $f['answer'] }}</x-faq-item>@endforeach</div>
            </section>
        @endif
</div>

@if($related->isNotEmpty())
    <section class="pb-8"><div class="container-page"><h2 class="text-heading">@heading('You might also like')</h2>
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">@foreach($related as $package)@include('partials.package-card', ['package' => $package])@endforeach</div>
    </div></section>
@endif
@endsection
