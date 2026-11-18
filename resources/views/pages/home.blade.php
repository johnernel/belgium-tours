@extends('layouts.app')

@section('title', $page->seo_title ?: $page->title)
@section('description', (string) ($page->meta_description ?? ''))
@section('hero', '1')
@if($page->noindex)@section('noindex', '1')@endif

@push('preload')
    <link rel="preload" as="image" href="{{ asset('images/photos/bruges-canal-800.webp') }}" imagesrcset="{{ asset('images/photos/bruges-canal-480.webp') }} 640w, {{ asset('images/photos/bruges-canal-800.webp') }} 1024w, {{ asset('images/photos/bruges-canal-1280.webp') }} 1600w" imagesizes="100vw" fetchpriority="high">
@endpush

@push('schema')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faqs->map(fn ($f) => [
            '@type' => 'Question', 'name' => $f->question,
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f->answer],
        ])->values(),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('footer_flush', '1')
@section('content')
@php $c = fn ($k) => $page->block($k); @endphp

{{-- 3. HERO --}}
@php
    // Headline: break after the first comma; the last word gets the italic amber treatment (as in the design reference).
    $headline = \App\Support\Heading::titleCase(trim($c('hero_h1') ?: 'Belgium, planned around you'));
    [$line1, $line2] = str_contains($headline, ',') ? array_map('trim', explode(',', $headline, 2)) : [$headline, null];
    $line1 .= $line2 ? ',' : '';
    $tail = $line2 ?? $line1;
    $cut = strrpos($tail, ' ');
    [$tailPlain, $tailEm] = $cut ? [substr($tail, 0, $cut), substr($tail, $cut + 1)] : ['', $tail];

    // Trust indicators for the marquee. Edit in Admin > Pages > Home (marquee_1 to marquee_4; badges are shared).
    $trust = array_values(array_filter([
        ['check', $c('marquee_1') ?: 'Free, no-obligation quote'],
        ['check', $c('marquee_2') ?: 'Tailor-made itineraries'],
        ['check', $c('marquee_3') ?: 'Self drive, chauffeur driven and coach tours'],
        ['check', $c('marquee_4') ?: 'Hotels, routes and transfers planned for you'],
        ['shield', $c('badge_1')],
        ['compass', $c('badge_2')],
        ['star', $c('badge_3')],
    ], fn ($t) => filled($t[1])));
@endphp
<section class="relative isolate -mt-[4.5rem] flex min-h-[42rem] flex-col overflow-hidden bg-ink text-white lg:min-h-[48rem]">
    {{-- Background slideshow: three photos crossfade, each with a slow zoom and pan (Ken Burns). CSS only, see app.css. --}}
    <div class="absolute inset-0 z-0 overflow-hidden bg-ink" aria-hidden="true">
        <x-photo base="images/photos/bruges-canal" alt="" sizes="100vw" :eager="true" class="hero-slide hero-slide-1 absolute inset-0 size-full" />
        <x-photo base="images/photos/brussels-grand-place" alt="" sizes="100vw" fetchpriority="low" class="hero-slide hero-slide-2 absolute inset-0 size-full" />
        <x-photo base="images/photos/gravensteen" alt="" sizes="100vw" fetchpriority="low" class="hero-slide hero-slide-3 absolute inset-0 size-full" />
    </div>
    <div class="absolute inset-0 z-[1] bg-gradient-to-b from-ink/55 via-ink/35 to-ink/85"></div>
    <div class="absolute inset-0 z-[1] bg-gradient-to-r from-ink/70 via-ink/25 to-transparent"></div>

    {{-- Same layout as the other pages: headline and description centred beside the main quiz form --}}
    <div class="container-page relative z-10 grid w-full flex-1 items-center gap-10 pb-6 pt-[calc(4.5rem+3rem)] sm:pb-8 lg:grid-cols-[1.1fr_0.9fr] lg:gap-14 lg:pt-[calc(4.5rem+2rem)]">
        <div>
            <h1 class="on-dark hero-rise font-serif font-medium !text-white max-w-[16ch] text-5xl leading-[1.02] sm:text-6xl lg:max-w-[14ch] lg:text-[4.5rem]">@heading($headline)</h1>
            <p class="hero-rise hero-rise-2 mt-8 max-w-lg text-lg text-white/90 sm:text-xl">{{ $c('hero_sub') }}</p>
        </div>

        <div class="hero-rise hero-rise-2 lg:w-full lg:max-w-[28rem] lg:justify-self-end">
            @include('partials.quiz')
        </div>
    </div>

    {{-- The trust-indicator marquee stays at the bottom of the hero --}}
    <div class="container-page relative z-10 pb-6 sm:pb-8">
        {{-- Trust indicators --}}
        <div class="marquee hero-rise hero-rise-3 overflow-hidden rounded-full bg-ink/45 py-3.5 ring-1 ring-white/20 backdrop-blur-md" role="region" aria-label="Why travellers trust us">
            <div class="marquee-track">
                @foreach([false, true] as $copy)
                    <ul class="flex shrink-0 items-center gap-x-10 pr-10" @if($copy) aria-hidden="true" @endif>
                        @foreach($trust as [$ic, $text])
                            <li class="inline-flex items-center gap-2 whitespace-nowrap text-sm font-normal text-white">@icon($ic, 'size-4 shrink-0 text-amber') {{ $text }}</li>
                        @endforeach
                    </ul>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- 4. TAILOR-MADE IN BELGIUM (shared with the About Us page) --}}
@include('partials.tailor-made')

{{-- 5. THREE WAYS TO TRAVEL: video background, tabs and journey carousels --}}
@include('partials.ways-to-travel', [
    'tourTypes' => $tourTypes,
    'eyebrow' => $c('ways_eyebrow') ?: 'Signature Journeys',
    'heading' => $c('ways_heading') ?: 'Three Ways to *Travel* Belgium',
    'sub' => $c('ways_sub') ?: 'Choose how you move. We handle everything else.',
])

{{-- 6. TOP DESTINATIONS: vertical tabs with a large image --}}
@include('partials.top-destinations', [
    'experiences' => $experiences,
    'eyebrow' => $c('dest_eyebrow') ?: 'Top Destinations',
    'heading' => $c('dest_heading') ?: 'Five things worth *slowing down* for.',
    'cta' => $c('dest_cta') ?: 'View All Destinations',
])

{{-- 6b. TRAVEL YOUR WAY: vertical tabs for the three tour types --}}
@include('partials.travel-your-way', [
    'tourTypes' => $tourTypes,
    'eyebrow' => $c('way_eyebrow') ?: 'Pick your pace',
    'heading' => $c('way_heading') ?: 'Travel *Your Way*',
])

{{-- 6c. WHY CHOOSE US: dusk background, features, optional confirmed stats --}}
@include('partials.why-choose-us')

{{-- 7. HOW IT WORKS (a real sequence, so numbered) --}}
@include('partials.how-it-works')

{{-- 7b. QUOTE BAND: photo band with the two main calls to action --}}
@include('partials.quote-band')

{{-- 8. TESTIMONIALS --}}
@include('partials.testimonials')

{{-- 8b. GALLERY: curved photo marquee --}}
@include('partials.gallery')

{{-- 9. FAQ --}}
@include('partials.faq')

{{-- 9a. THE JOURNEY NEVER ENDS: interactive Belgium map with photo polaroids --}}
@include('partials.journey-map')

{{-- 9b. BLOG: latest Journal articles --}}
@include('partials.blog-section')

@endsection
