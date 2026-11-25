@extends('layouts.app')

{{-- Home page. This is the redesigned layout (it was the "Homepage Test" trial): contact strip + mega-menu header (partials/header-test), photo hero with the quiz card,
     feature strip, tailor-made, tour type cards, package carousels, why choose us, stress-free band, testimonials with video, destinations, payments band, plan band and the
     matching footer (partials/footer-test). Same data and copy as before (Admin > Pages > Home). The previous design is kept at /homepage-test (noindex). --}}
@section('title', $page->seo_title ?: $page->title)
@section('description', (string) ($page->meta_description ?? ''))
@section('hero', '1')
@if($page->noindex)@section('noindex', '1')@endif
@section('header_view', 'partials.header-test')
@section('footer_view', 'partials.footer-test')
@section('footer_flush', '1')
@push('preload')
    <link rel="preload" as="image" href="{{ asset('images/photos/hero-dinant-quay-1280.webp') }}" imagesrcset="{{ asset('images/photos/hero-dinant-quay-480.webp') }} 480w, {{ asset('images/photos/hero-dinant-quay-800.webp') }} 800w, {{ asset('images/photos/hero-dinant-quay-1280.webp') }} 1280w, {{ asset('images/photos/hero-dinant-quay-1600.webp') }} 1600w" imagesizes="(max-width: 1023px) 110vw, 100vw" fetchpriority="high">
@endpush

@section('content')
@php
    $c = fn ($k) => $page->block($k);

    $headline = \App\Support\Heading::titleCase(trim($c('hero_h1') ?: 'Belgium, planned around you'));

    $trust = array_values(array_filter([
        ['check', $c('marquee_1') ?: 'Free, no-obligation quote'],
        ['compass', $c('marquee_2') ?: 'Tailor-made itineraries'],
        ['car', $c('marquee_3') ?: 'Self drive, chauffeur driven and coach tours'],
        ['bed', $c('marquee_4') ?: 'Hotels, routes and transfers planned for you'],
    ], fn ($t) => filled($t[1])));

    $nTours = fn ($n) => $n === 1 ? '1 tour' : $n.' tours';
    $why = trim($c('why_heading') ?: 'Every journey, personally crafted.');
    $wcu = collect([1, 2, 3, 4])->map(fn ($n) => ['icon' => ['svg:local-expertise', 'svg:tailor-made', 'svg:handpicked-stays', 'shield'][$n - 1], 'title' => $c("wcu_{$n}_title"), 'text' => $c("wcu_{$n}_text")])->filter(fn ($f) => $f['title']);
    $steps = collect([1, 2, 3])->map(fn ($n) => ['title' => $c("step_{$n}_title"), 'text' => $c("step_{$n}_text")])->filter(fn ($s) => $s['title']);
    $quotes = $testimonials->take(3);
@endphp

{{-- 1. HERO: photo slideshow; pill, two-line headline with a short accent bar, text and highlights on the left; the quiz card on the right --}}
@php
    [$hl1, $hl2] = str_contains($headline, ',') ? array_map('trim', explode(',', $headline, 2)) : [$headline, null];
    if ($hl2 === null) { $w = explode(' ', $headline); $half = (int) ceil(count($w) / 2); $hl1 = implode(' ', array_slice($w, 0, $half)); $hl2 = implode(' ', array_slice($w, $half)); } else { $hl1 .= ','; }
@endphp
<section class="relative isolate -mt-[4.5rem] flex min-h-[44rem] flex-col overflow-hidden bg-ink text-white lg:-mt-[8rem] lg:min-h-[50rem]">
    <div class="absolute inset-0 z-0 overflow-hidden bg-ink" aria-hidden="true">
        <x-photo base="images/photos/hero-dinant-quay" alt="" sizes="(max-width: 1023px) 110vw, 100vw" :ratio="[1600, 900]" :eager="true" class="hero-slide hero-slide-1 absolute inset-0 size-full" />
        <x-photo base="images/photos/hero-antwerp-markt" alt="" sizes="(max-width: 1023px) 110vw, 100vw" :ratio="[1600, 900]" fetchpriority="low" :defer="true" class="hero-slide hero-slide-2 absolute inset-0 size-full" />
        <x-photo base="images/photos/hero-bruges-markt" alt="" sizes="(max-width: 1023px) 110vw, 100vw" :ratio="[1600, 900]" fetchpriority="low" :defer="true" class="hero-slide hero-slide-3 absolute inset-0 size-full" />
    </div>
    <div class="absolute inset-0 z-[1] bg-gradient-to-b from-ink/33 to-ink/26"></div>

    <div class="container-wide relative z-10 grid w-full flex-1 items-end gap-10 pb-12 pt-[calc(4.5rem+2.5rem)] lg:grid-cols-[1.15fr_0.85fr] lg:items-center lg:gap-14 lg:pb-14 lg:pt-[calc(8rem+1.5rem)]">
        <div class="lg:pt-24 max-sm:text-center">
            <p class="hero-rise inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-medium text-ink shadow-card">@icon('star', 'size-4 text-amber-deep') Authentic Trips, Belgian Heart</p>
            <h1 class="on-dark hero-rise mt-6 [text-shadow:0_2px_4px_rgb(0_0_0/0.45),0_4px_24px_rgb(0_0_0/0.5)] font-serif text-[2.65rem] font-medium leading-[1.05] !text-white sm:text-6xl lg:text-[4.25rem]">
                {{ $hl1 }}<br><span class="relative inline-block text-white">{{ $hl2 }}
                    {{-- Brush-style underline under the second line: a slightly rising stroke (about 6-8px at its thickest) that is thickest at the left and tapers to a point on the right, in the exact orange #FF7518 (the amber token) --}}
                    <svg viewBox="0 0 300 14" preserveAspectRatio="none" class="pointer-events-none absolute left-0 top-full -mt-1 h-3 w-full text-amber max-sm:left-1/2 max-sm:w-[72%] max-sm:-translate-x-1/2 sm:h-3.5 lg:-mt-0.5 lg:h-4" fill="currentColor" aria-hidden="true"><path d="M1 6.5C70 3 190 1 299 1.4 193 4.6 78 10.6 1 13Z"/></svg>
                </span>
            </h1>
            <p class="hero-rise hero-rise-2 mt-9 max-w-xl text-lg text-white [text-shadow:0_2px_4px_rgb(0_0_0/0.45),0_4px_24px_rgb(0_0_0/0.5)] max-sm:hidden sm:text-xl">{{ $c('hero_sub') }}</p>

        </div>
        <div class="hero-rise hero-rise-2 w-full lg:max-w-[32rem] lg:justify-self-end">
            @include('partials.quiz-light')
        </div>
    </div>
</section>
{{-- 2. FEATURE STRIP (layout copied from the client's reference): white band, four items, each a square icon tile with the title and a two-line description beside it, thin dividers between items --}}
@php
    $strip = [
        ['wallet', 'Honest, Upfront Pricing', 'Clearly outlined details for complete peace of mind'],
        ['flag', 'More Belgium for Less', 'Carefully crafted trips designed for maximum value'],
        ['shield', 'Safety First', 'Explore Belgium with complete peace of mind.'],
        ['headset', 'Customer Support', 'Dedicated personal support from start to finish.'],
    ];
@endphp
<section class="relative z-10 bg-white max-md:hidden" aria-label="Why travel with us">
    <div class="container-wide grid gap-y-8 py-9 sm:grid-cols-2 xl:grid-cols-4 lg:py-10">
        @foreach($strip as [$ic, $title, $text])
            <div class="flex items-start gap-3 {{ $loop->first ? 'xl:pr-5' : ($loop->last ? 'xl:border-l xl:border-ink/15 xl:pl-5' : 'xl:border-l xl:border-ink/15 xl:px-5') }}">
                <span class="grid size-12 shrink-0 place-items-center bg-slate text-aqua-light">@icon($ic, 'size-6')</span>
                <div class="min-w-0">
                    <h3 class="font-display text-lg leading-snug text-ink xl:text-xl xl:whitespace-nowrap">{{ $title }}</h3>
                    <p class="mt-1 text-[0.95rem] leading-snug text-slate">{{ $text }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>
{{-- 3. ABOUT / TAILOR-MADE: big photo with a small overlapping one, copy and checklist --}}
<section x-ignore data-lazy-alpine class="cv-auto bg-ivory py-16 max-md:hidden sm:py-24" aria-labelledby="ht-about">
    <div class="container-wide grid items-center gap-12 lg:grid-cols-[0.95fr_1.05fr] lg:gap-20">
        <div class="relative pb-10 pr-8 sm:pr-14">
            <div class="relative aspect-[4/5] overflow-hidden shadow-card">
                <x-photo base="images/photos/tailor-canal" alt="Brick gabled houses beside a canal, with a cathedral tower behind them at sunset" :widths="[480, 800, 1280, 1600]" sizes="(min-width:1024px) 520px, 80vw" fetchpriority="low" :ratio="[1600, 900]" class="absolute inset-0 size-full" />
            </div>
            <div class="absolute bottom-0 right-0 aspect-square w-2/5 overflow-hidden border-4 border-ivory shadow-lift">
                <x-photo base="images/photos/tailor-square" alt="Visitors on a sunlit town square below a tall cathedral tower" :widths="[480, 800]" sizes="240px" fetchpriority="low" :ratio="[1600, 900]" class="absolute inset-0 size-full" />
            </div>
        </div>
        <div>
            @include('partials.ht-eyebrow', ['label' => $c('why_eyebrow') ?: 'Tailor-made in Belgium'])
            <h2 id="ht-about" class="mt-5 font-serif text-heading">@heading($why)</h2>
            <p class="mt-5 text-lg leading-relaxed text-slate">{{ $c('why_sub') }}</p>
            <ul class="mt-7 space-y-4">
                @foreach([1, 2, 3] as $n)
                    @if($c("why_{$n}_title"))
                        <li class="flex items-start gap-3">
                            <span class="mt-1 grid size-6 shrink-0 place-items-center rounded-full bg-aqua text-ink">@icon('check', 'size-3.5')</span>
                            <div><h3 class="font-serif text-xl font-normal leading-snug">{{ $c("why_{$n}_title") }}</h3><p class="mt-0.5 text-[0.95rem] text-slate">{{ $c("why_{$n}_text") }}</p></div>
                        </li>
                    @endif
                @endforeach
            </ul>
            <div class="mt-8"><x-button square variant="cta" size="lg" icon="arrow-right" @click="$store.modal.open()">{{ $c('why_cta') ?: 'Plan My Journey' }}</x-button></div>
        </div>
    </div>
</section>

{{-- 4. CHOOSE YOUR JOURNEY (card layout copied from the client's reference): heading block, then a full-width, edge-to-edge row of three equal photo cards.
     Each card: title, one-line tagline, a small uppercase "Best for" line and an outlined square "Get quote" button, all bottom-left over the photo. --}}
@php
    $bestFor = ['self-drive' => 'Couples · Families · Independent travelers', 'chauffeur-driven' => 'Couples · Solo travelers · Luxury seekers', 'coach-tours' => 'Solo travelers · Couples · Groups'];
@endphp
<section x-ignore data-lazy-alpine class="cv-auto bg-white pb-16 pt-16 sm:pb-24 sm:pt-24" aria-labelledby="ht-ways">
    <div class="container-page">
        <div class="mx-auto max-w-4xl text-center">
            @include('partials.ht-eyebrow', ['label' => $c('ways_eyebrow') ?: 'Choose your journey', 'center' => true])
            <h2 id="ht-ways" class="mt-5 font-serif text-heading md:whitespace-nowrap">@heading($c('ways_heading') ?: 'Three Ways to *Travel* Belgium')</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-slate">{{ $c('ways_sub') ?: 'Choose how you move. We handle everything else.' }}</p>
        </div>
    </div>
    {{-- Phones: a swipeable scroll-snap carousel (one card at a time, the next card peeks 20px in from the right) with dots; from md up: the three equal columns --}}
    <div class="mt-12" x-data="pageCarousel()">
    <div x-ref="track" @scroll.passive="update()" tabindex="0" aria-label="Ways to travel, swipe to see more" class="relative flex snap-x snap-mandatory gap-3 overflow-x-auto scroll-smooth pr-8 [scrollbar-width:none] motion-reduce:scroll-auto md:grid md:snap-none md:grid-cols-3 md:gap-5 md:overflow-visible md:pr-0 [&::-webkit-scrollbar]:hidden">
        @foreach($tourTypes as $t)
            <article class="group relative isolate flex w-[calc(100vw-2rem)] shrink-0 snap-start min-h-[26rem] flex-col justify-end overflow-hidden bg-ink p-6 text-white sm:min-h-[30rem] lg:min-h-[32rem] lg:p-8 md:w-auto md:shrink">
                @if($t->image)<x-photo :base="$t->image" alt="" :widths="[480, 800, 1280, 1600]" sizes="(min-width:768px) 34vw, 100vw" fetchpriority="low" :ratio="[1600, 1067]" class="absolute inset-0 -z-20 size-full transition-transform duration-700 group-hover:scale-105" />@endif
                <div class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/90 via-ink/30 to-transparent"></div>
                <h3 class="font-display text-3xl font-semibold leading-tight text-white lg:text-[2rem]"><a href="{{ route('tours.show', $t->slug) }}" class="hover:text-amber">{{ $t->name }}</a></h3>
                @if($t->menu_text)<p class="mt-1 text-[0.95rem] text-white">{{ $t->menu_text }}</p>@endif
                @if(isset($bestFor[$t->slug]))<p class="mt-5 text-xs font-medium uppercase tracking-[0.08em] text-white/85"><span class="text-white/70">Best for</span> · {{ $bestFor[$t->slug] }}</p>@endif
                <div class="mt-4">
                    <button type="button" @click="$store.modal.open({ tourType: {{ \Illuminate\Support\Js::from($t->name) }} })" class="inline-flex min-h-11 items-center border border-white px-6 text-sm font-semibold uppercase tracking-wide text-white transition hover:bg-white hover:text-ink">Get Quote</button>
                </div>
            </article>
        @endforeach
    </div>
    {{-- Pagination dots (same pills as the package carousels), phones only --}}
    <div x-show="pages > 1" x-cloak class="mt-6 md:hidden">
        <div class="flex items-center justify-center gap-4">
            <button type="button" @click="prev()" :disabled="page === 0" aria-label="Previous card" class="grid size-11 place-items-center rounded-full border-2 border-ink/25 text-ink transition hover:border-ink hover:bg-ink hover:text-cream disabled:pointer-events-none disabled:opacity-30">@icon('arrow-right', 'size-5 rotate-180')</button>
            <div class="flex items-center gap-0" role="group" aria-label="Choose a card">
                <template x-for="p in pages" :key="p">
                    <button type="button" @click="go(p - 1)" :aria-label="'Card ' + p" :aria-current="page === p - 1 ? 'true' : null" class="group grid h-6 place-items-center" :class="page === p - 1 ? 'w-14' : 'w-6'"><span class="block h-2 rounded-full transition-all duration-300" :class="page === p - 1 ? 'w-12 bg-amber' : 'w-2.5 bg-ink/20 group-hover:bg-ink/40'"></span></button>
                </template>
            </div>
            <button type="button" @click="next()" :disabled="page >= pages - 1" aria-label="Next card" class="grid size-11 place-items-center rounded-full border-2 border-ink/25 text-ink transition hover:border-ink hover:bg-ink hover:text-cream disabled:pointer-events-none disabled:opacity-30">@icon('arrow-right', 'size-5')</button>
        </div>
        <p class="mt-2 text-center text-xs font-semibold uppercase tracking-[0.2em] text-slate" aria-live="polite"><span x-text="page + 1"></span> / <span x-text="pages"></span></p>
    </div>
    </div>
</section>
{{-- 5. PACKAGES: one swipeable carousel per tour type (3 cards per view on desktop, 2 on tablet, 1 on mobile), page indicator, no autoplay --}}
<section x-ignore data-lazy-alpine class="cv-auto bg-ivory py-16 sm:py-24" aria-labelledby="ht-packages">
    <div class="container-wide space-y-16">
        <div class="mx-auto max-w-2xl text-center">
            @include('partials.ht-eyebrow', ['label' => 'Our Packages', 'center' => true])
            <h2 id="ht-packages" class="mt-5 font-serif text-heading">@heading('Trips for *Every* Traveler')</h2>
        </div>
        @foreach($tourTypes as $t)
            @if($t->packages->isNotEmpty())
                <div x-data="pageCarousel()" role="region" aria-roledescription="carousel" aria-label="{{ $t->name }} trips">
                    <div class="flex flex-wrap items-end justify-between gap-4 border-b border-ink/15 pb-4">
                        <h3 class="flex items-center gap-3 whitespace-nowrap font-display text-xl sm:text-3xl"><svg viewBox="0 0 15 13" class="h-[1.15rem] w-[1.35rem] shrink-0 rounded-[3px] shadow-sm ring-1 ring-ink/10" role="img" aria-label="Belgium flag"><rect width="5" height="13" fill="#000"/><rect x="5" width="5" height="13" fill="#FAE042"/><rect x="10" width="5" height="13" fill="#ED2939"/></svg><span>{{ $t->name }} Packages</span></h3>
                        <a href="{{ route('tours.show', $t->slug) }}" class="inline-flex min-h-11 items-center gap-2 border-2 border-ink px-5 text-sm font-semibold hover:bg-ink hover:text-cream max-sm:hidden">View all @icon('arrow-right', 'size-4')</a>
                    </div>
                    <div x-ref="track" @scroll.passive="update()" @keydown.arrow-left.prevent="prev()" @keydown.arrow-right.prevent="next()" tabindex="0" aria-label="{{ $t->name }} packages, swipe or use the arrows"
                         class="relative mt-8 flex snap-x snap-mandatory items-stretch gap-3 overflow-x-auto scroll-smooth pb-4 max-sm:after:w-5 max-sm:after:shrink-0 max-sm:after:content-[''] sm:gap-6 [scrollbar-width:none] motion-reduce:scroll-auto [&::-webkit-scrollbar]:hidden">
                        @foreach($t->packages as $package)
                            <div class="flex w-[calc(100%-2rem)] shrink-0 snap-start sm:w-[calc((100%-1.5rem)/2)] lg:w-[calc((100%-3rem)/3)] [&>*]:w-full" role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $loop->count }}">
                                @include('partials.package-card', ['package' => $package, 'square' => true])
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination: round arrows either side of a row of pill segments (the current page stretches and turns amber), plus a counter --}}
                    <div x-show="pages > 1" x-cloak class="mt-6 flex items-center justify-center gap-4">
                        <button type="button" @click="prev()" :disabled="page === 0" aria-label="Previous page"
                                class="grid size-11 place-items-center rounded-full border-2 border-ink/25 text-ink transition hover:border-ink hover:bg-ink hover:text-cream disabled:pointer-events-none disabled:opacity-30">@icon('arrow-right', 'size-5 rotate-180')</button>
                        <div class="flex items-center gap-0" role="group" aria-label="Choose a page">
                            <template x-for="p in pages" :key="p">
                                <button type="button" @click="go(p - 1)" :aria-label="'Page ' + p" :aria-current="page === p - 1 ? 'true' : null"
                                        class="group grid h-6 place-items-center" :class="page === p - 1 ? 'w-14' : 'w-6'"><span class="block h-2 rounded-full transition-all duration-300" :class="page === p - 1 ? 'w-12 bg-amber' : 'w-2.5 bg-ink/20 group-hover:bg-ink/40'"></span></button>
                            </template>
                        </div>
                        <button type="button" @click="next()" :disabled="page >= pages - 1" aria-label="Next page"
                                class="grid size-11 place-items-center rounded-full border-2 border-ink/25 text-ink transition hover:border-ink hover:bg-ink hover:text-cream disabled:pointer-events-none disabled:opacity-30">@icon('arrow-right', 'size-5')</button>
                    </div>
                    <p x-show="pages > 1" x-cloak class="mt-2 text-center text-xs font-semibold uppercase tracking-[0.2em] text-slate" aria-live="polite"><span x-text="page + 1"></span> / <span x-text="pages"></span></p>
                    {{-- Phones only: the "View all" link moves from the heading row to below the carousel --}}
                    <a href="{{ route('tours.show', $t->slug) }}" class="mx-auto mt-5 flex min-h-10 w-fit items-center justify-center gap-2 border-2 border-ink px-5 text-xs font-semibold uppercase tracking-[0.12em] hover:bg-ink hover:text-cream sm:hidden">View all packages @icon('arrow-right', 'size-4')<span class="sr-only"> {{ $t->name }}</span></a>
                </div>
            @endif
        @endforeach
    </div>
</section>
{{-- 6. WHY CHOOSE US: dusk band, photo plus a 2x2 grid of reasons --}}
@if($wcu->isNotEmpty())
<section x-ignore data-lazy-alpine class="cv-auto relative isolate overflow-hidden bg-mist py-16 text-ink sm:py-24" aria-labelledby="ht-wcu">
    {{-- Decorative Antwerp skyline (client-supplied image turned into a soft tint), bottom right, behind everything; hidden on phones --}}
    <img src="{{ asset('images/decor/antwerp-skyline.webp') }}" alt="" aria-hidden="true" width="927" height="617" loading="lazy" class="pointer-events-none absolute bottom-0 right-0 -z-10 hidden h-[24rem] w-auto max-w-[60%] select-none opacity-60 md:block lg:h-[28rem]">
    <div class="container-wide">
        <div class="flex flex-wrap items-end justify-between gap-6 max-md:justify-center max-md:text-center">
            <div class="max-w-2xl">
                @include('partials.ht-eyebrow', ['label' => $c('wcu_eyebrow') ?: 'Why choose us', 'mobileCenter' => true])
                <h2 id="ht-wcu" class="mt-5 font-serif text-heading">@heading($c('wcu_heading') ?: 'Why Choose *Us*')</h2>
                @if($c('wcu_sub'))<p class="mt-4 text-lg text-ink/85">{{ $c('wcu_sub') }}</p>@endif
            </div>
            <x-button square variant="cta" size="lg" icon="arrow-right" @click="$store.modal.open()">Start Your Journey</x-button>
        </div>
        <div class="mt-12 grid gap-6 lg:grid-cols-[1fr_1.1fr]">
            <div class="relative min-h-[20rem] overflow-hidden">
                <x-photo base="images/photos/gravensteen" alt="" :widths="[480, 800, 1280]" sizes="(min-width:1024px) 45vw, 92vw" fetchpriority="low" :ratio="[1600, 1044]" class="absolute inset-0 size-full" />
            </div>
            {{-- Phones: a swipeable scroll-snap carousel (one card at a time, the next card peeks 20px in from the right) with pill dots like the package carousels; from sm up the 2x2 grid is unchanged --}}
            <div class="min-w-0" x-data="pageCarousel()">
                <div x-ref="track" @scroll.passive="update()" tabindex="0" aria-label="Why travel with us, swipe to see more"
                     class="relative grid gap-4 sm:grid-cols-2 max-sm:flex max-sm:snap-x max-sm:snap-mandatory max-sm:gap-3 max-sm:overflow-x-auto max-sm:scroll-smooth max-sm:pb-1 max-sm:[scrollbar-width:none] max-sm:after:w-5 max-sm:after:shrink-0 max-sm:after:content-[''] max-sm:motion-reduce:scroll-auto max-sm:[&::-webkit-scrollbar]:hidden">
                    @foreach($wcu as $f)
                        <div class="border border-mist-deep bg-white p-6 shadow-[0_8px_22px_-14px_var(--color-mist-deep)] max-sm:w-[calc(100%-2rem)] max-sm:shrink-0 max-sm:snap-start">
                            <span class="grid size-12 place-items-center rounded-full bg-mint text-teal-deep">@if(str_starts_with($f['icon'], 'svg:'))<span class="qicon size-7" style="--qi: url({{ asset('images/why-icons/'.substr($f['icon'], 4).'.svg') }})" aria-hidden="true"></span>@else @icon($f['icon'], 'size-6')@endif</span>
                            <h3 class="mt-4 font-display text-xl text-ink">{{ $f['title'] }}</h3>
                            <p class="mt-2 text-[0.95rem] text-ink/80">{{ $f['text'] }}</p>
                        </div>
                    @endforeach
                </div>
                <div x-show="pages > 1" x-cloak class="mt-5 flex items-center justify-center gap-0 sm:hidden" role="group" aria-label="Choose a card">
                    <template x-for="p in pages" :key="p">
                        <button type="button" @click="go(p - 1)" :aria-label="'Card ' + p" :aria-current="page === p - 1 ? 'true' : null" class="group grid h-6 place-items-center" :class="page === p - 1 ? 'w-14' : 'w-6'"><span class="block h-2 rounded-full transition-all duration-300" :class="page === p - 1 ? 'w-12 bg-amber' : 'w-2.5 bg-ink/20 group-hover:bg-ink/40'"></span></button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

@include('partials.page-stress-free')
@include('partials.page-payments')
@include('partials.page-testimonials')
{{-- 9. DESTINATIONS: photo grid, tour counts come from the database --}}
<section x-ignore data-lazy-alpine class="cv-auto bg-ivory py-16 sm:py-24" aria-labelledby="ht-dest">
    <div class="container-wide">
        <div class="mx-auto max-w-2xl text-center">
            @include('partials.ht-eyebrow', ['label' => $c('dest_eyebrow') ?: 'Top destinations', 'center' => true])
            <h2 id="ht-dest" class="mt-5 font-serif text-heading">@heading('Discover the *Best of Belgium*')</h2>
        </div>
        {{-- Phones (below sm): a swipeable scroll-snap carousel, one image card at a time with the next card peeking 20px in from the right; from sm up the grid is unchanged --}}
        <div class="mt-12 min-w-0" x-data="pageCarousel()">
        <div x-ref="track" @scroll.passive="update()" tabindex="0" aria-label="Destinations, swipe to see more" class="relative grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-3 max-sm:flex max-sm:snap-x max-sm:snap-mandatory max-sm:gap-3 max-sm:overflow-x-auto max-sm:scroll-smooth max-sm:[scrollbar-width:none] max-sm:after:w-5 max-sm:after:shrink-0 max-sm:after:content-['] max-sm:motion-reduce:scroll-auto max-sm:[&::-webkit-scrollbar]:hidden">
            @foreach($experiences as $e)
                <a href="{{ route('packages.index', ['experience' => $e->slug]) }}" class="group relative isolate flex aspect-[4/3] items-end overflow-hidden bg-ink p-4 text-white max-sm:w-[calc(100%-2rem)] max-sm:shrink-0 max-sm:snap-start sm:p-6 {{ $loop->first ? 'sm:aspect-[4/3]' : '' }}">
                    @if($e->image)<x-photo :base="$e->image" :alt="$e->image_alt ?: ''" :widths="[480, 800, 1280]" sizes="(min-width:1024px) 33vw, 50vw" fetchpriority="low" :ratio="[1600, 1067]" class="absolute inset-0 -z-20 size-full transition-transform duration-700 group-hover:scale-105" style="object-position: {{ $e->image_focus ?: '50% 50%' }}" />@endif
                    <span class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/85 via-ink/20 to-transparent"></span>
                    <span class="flex w-full items-end justify-between gap-3">
                        <span><span class="block font-display text-2xl sm:text-3xl">{{ $e->name }}</span><span class="text-sm text-white/85">{{ $nTours($e->packages_count) }}</span></span>
                        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-aqua text-ink transition group-hover:bg-amber">@icon('arrow-right', 'size-4')</span>
                    </span>
                </a>
            @endforeach
        </div>
        {{-- Pagination dots (phones only), same pills as the package carousels --}}
        <div x-show="pages > 1" x-cloak class="mt-6 flex items-center justify-center gap-0 sm:hidden" role="group" aria-label="Choose a destination">
            <template x-for="p in pages" :key="p">
                <button type="button" @click="go(p - 1)" :aria-label="'Destination ' + p" :aria-current="page === p - 1 ? 'true' : null" class="group grid h-6 place-items-center" :class="page === p - 1 ? 'w-14' : 'w-6'"><span class="block h-2 rounded-full transition-all duration-300" :class="page === p - 1 ? 'w-12 bg-amber' : 'w-2.5 bg-ink/20 group-hover:bg-ink/40'"></span></button>
            </template>
        </div>
        </div>
    </div>
</section>

{{-- 10. PLAN YOUR TRIP (layout copied from the client's reference): pale band, centred label, heading, intro, three small reassurance points, one button,
     and a faded skyline silhouette along the bottom edge (Belgian step gables and belfries instead of mountains) --}}
<section x-ignore data-lazy-alpine class="cv-auto relative isolate overflow-hidden bg-white py-16 sm:py-24" aria-labelledby="ht-plan">
    <div class="absolute inset-0 -z-20 bg-sky-soft/55" aria-hidden="true"></div>
    <div class="container-wide text-center">
        @include('partials.ht-eyebrow', ['label' => 'Craft your trip', 'center' => true])
        <h2 id="ht-plan" class="mt-5 font-serif text-heading">@heading('Plan Your Belgium Trip')</h2>
        <p class="mx-auto mt-5 max-w-2xl text-lg text-ink">Let Belgium's stories become yours. Our team of local experts is ready to craft something extraordinary, built entirely around you.</p>
        <ul class="mt-7 flex flex-wrap items-center justify-center gap-x-7 gap-y-2 text-sm text-ink">
            <li class="inline-flex items-center gap-2">@icon('lock', 'size-4 text-amber-deep') Secure booking</li>
            <li class="inline-flex items-center gap-2">@icon('circle-check', 'size-4 text-amber-deep') Free quote</li>
            <li class="inline-flex items-center gap-2">@icon('shield', 'size-4 text-amber-deep') No obligation</li>
        </ul>
        <div class="mt-8"><x-button square variant="cta" size="lg" @click="$store.modal.open()">Plan My Belgium Trip</x-button></div>
    </div>
    <svg viewBox="0 0 1440 120" preserveAspectRatio="xMidYMax slice" class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-28 w-full text-aqua/60 [mask-image:linear-gradient(to_bottom,#000_45%,transparent)] sm:h-36" fill="currentColor" aria-hidden="true"><path d="M0 120 V90 L0 75 V68 H11 V61 H18 V54 H25 V47 H32 V40 H39 V47 H46 V54 H53 V61 H60 V68 H67 V75 H74 V75 H78 L78 74 V67 H89 V60 H96 V53 H103 V46 H110 V53 H117 V60 H124 V67 H131 V74 H138 V74 H142 L142 71 V64 H153 V57 H160 V50 H167 V43 H174 V36 H181 V43 H188 V50 H195 V57 H202 V64 H209 V71 H216 V71 H220 L220 67 H228 V63 L238 33 L248 63 V67 H256 L256 67 H264 V63 L274 33 L284 63 V67 H292 L292 79 V72 H303 V65 H310 V58 H317 V51 H324 V44 H331 V51 H338 V58 H345 V65 H352 V72 H359 V79 H366 V79 H370 L370 70 H378 V66 L388 36 L398 66 V70 H406 L406 80 H414 V76 L424 46 L434 76 V80 H442 L442 83 V76 H453 V69 H460 V62 H467 V55 H474 V62 H481 V69 H488 V76 H495 V83 H502 V83 H506 L506 86 V79 H517 V72 H524 V65 H531 V58 H538 V65 H545 V72 H552 V79 H559 V86 H566 V86 H570 L570 80 H578 V76 L588 46 L598 76 V80 H606 L606 73 V66 H617 V59 H624 V52 H631 V45 H638 V38 H645 V45 H652 V52 H659 V59 H666 V66 H673 V73 H680 V73 H684 L684 84 V77 H695 V70 H702 V63 H709 V70 H716 V77 H723 V84 H730 V84 H734 L734 72 V65 H745 V58 H752 V51 H759 V58 H766 V65 H773 V72 H780 V72 H784 L784 70 V63 H795 V56 H802 V49 H809 V42 H816 V49 H823 V56 H830 V63 H837 V70 H844 V70 H848 L848 61 H856 V57 L866 27 L876 57 V61 H884 L884 76 V69 H895 V62 H902 V55 H909 V62 H916 V69 H923 V76 H930 V76 H934 L934 59 H942 V55 L952 25 L962 55 V59 H970 L970 84 V77 H981 V70 H988 V63 H995 V56 H1002 V49 H1009 V56 H1016 V63 H1023 V70 H1030 V77 H1037 V84 H1044 V84 H1048 L1048 74 V67 H1059 V60 H1066 V53 H1073 V46 H1080 V39 H1087 V46 H1094 V53 H1101 V60 H1108 V67 H1115 V74 H1122 V74 H1126 L1126 63 H1134 V59 L1144 29 L1154 59 V63 H1162 L1162 60 H1170 V56 L1180 26 L1190 56 V60 H1198 L1198 80 V73 H1209 V66 H1216 V59 H1223 V52 H1230 V59 H1237 V66 H1244 V73 H1251 V80 H1258 V80 H1262 L1262 78 V71 H1273 V64 H1280 V57 H1287 V64 H1294 V71 H1301 V78 H1308 V78 H1312 L1312 84 V77 H1323 V70 H1330 V63 H1337 V70 H1344 V77 H1351 V84 H1358 V84 H1362 L1362 72 H1370 V68 L1380 38 L1390 68 V72 H1398 L1398 76 V69 H1409 V62 H1416 V55 H1423 V48 H1430 V41 H1437 V48 H1444 V55 H1451 V62 H1458 V69 H1465 V76 H1472 V76 H1476 V120 Z"/></svg>
</section>
@endsection
