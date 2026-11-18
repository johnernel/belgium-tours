@extends('layouts.app')

{{-- "Homepage Test": a design trial of the home page. SAME content, packages and settings as the real home page (Admin > Pages > Home);
     only the layout and styling differ. Reachable at /homepage-test, hidden from search engines and the sitemap. --}}
@section('title', 'Homepage Test | '.$site->brand)
@section('description', (string) ($page->meta_description ?? ''))
@section('hero', '1')
@section('noindex', '1')
@section('header_view', 'partials.header-test')
@section('footer_view', 'partials.footer-test')
@section('footer_flush', '1')

@push('preload')
    <link rel="preload" as="image" href="{{ asset('images/photos/bruges-canal-800.webp') }}" imagesrcset="{{ asset('images/photos/bruges-canal-480.webp') }} 640w, {{ asset('images/photos/bruges-canal-800.webp') }} 1024w, {{ asset('images/photos/bruges-canal-1280.webp') }} 1600w" imagesizes="100vw" fetchpriority="high">
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
    $badges = array_values(array_filter([$c('badge_1'), $c('badge_2'), $c('badge_3')]));

    $nTours = fn ($n) => $n === 1 ? '1 tour' : $n.' tours';
    $why = trim($c('why_heading') ?: 'Every journey, personally crafted.');
    $wcu = collect([1, 2, 3, 4])->map(fn ($n) => ['icon' => ['map-pin', 'compass', 'bed', 'shield'][$n - 1], 'title' => $c("wcu_{$n}_title"), 'text' => $c("wcu_{$n}_text")])->filter(fn ($f) => $f['title']);
    $steps = collect([1, 2, 3])->map(fn ($n) => ['title' => $c("step_{$n}_title"), 'text' => $c("step_{$n}_text")])->filter(fn ($s) => $s['title']);
    $quotes = $testimonials->take(3);
@endphp

{{-- 1. HERO: photo slideshow; pill, two-line headline with a short accent bar, text and highlights on the left; the quiz card on the right --}}
@php
    [$hl1, $hl2] = str_contains($headline, ',') ? array_map('trim', explode(',', $headline, 2)) : [$headline, null];
    if ($hl2 === null) { $w = explode(' ', $headline); $half = (int) ceil(count($w) / 2); $hl1 = implode(' ', array_slice($w, 0, $half)); $hl2 = implode(' ', array_slice($w, $half)); } else { $hl1 .= ','; }
@endphp
<section class="relative isolate -mt-[4.5rem] flex min-h-[44rem] flex-col overflow-hidden bg-ink text-white lg:-mt-[9rem] lg:min-h-[50rem]">
    <div class="absolute inset-0 z-0 overflow-hidden bg-ink" aria-hidden="true">
        <x-photo base="images/photos/bruges-canal" alt="" sizes="100vw" :eager="true" class="hero-slide hero-slide-1 absolute inset-0 size-full" />
        <x-photo base="images/photos/brussels-grand-place" alt="" sizes="100vw" fetchpriority="low" class="hero-slide hero-slide-2 absolute inset-0 size-full" />
        <x-photo base="images/photos/gravensteen" alt="" sizes="100vw" fetchpriority="low" class="hero-slide hero-slide-3 absolute inset-0 size-full" />
    </div>
    <div class="absolute inset-0 z-[1] bg-gradient-to-b from-ink/60 via-ink/45 to-ink/85"></div>
    <div class="absolute inset-0 z-[1] bg-gradient-to-r from-ink/80 via-ink/40 to-ink/10"></div>

    <div class="container-page relative z-10 grid w-full flex-1 items-end gap-10 pb-12 pt-[calc(4.5rem+2.5rem)] lg:grid-cols-[1.15fr_0.85fr] lg:items-center lg:gap-14 lg:pb-14 lg:pt-[calc(9rem+1.5rem)]">
        <div class="lg:pt-24">
            @if($badges)
                <p class="hero-rise inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-medium text-ink shadow-card">@icon('star', 'size-4 text-amber-deep') {{ $badges[0] }}</p>
            @endif
            <h1 class="on-dark hero-rise mt-6 font-serif text-5xl font-medium leading-[1.05] !text-white sm:text-6xl lg:text-[4.25rem]">
                {{ $hl1 }}<br><span class="text-amber">{{ $hl2 }}</span>
            </h1>
            <span class="hero-rise mt-4 block h-0.5 w-24 bg-amber" aria-hidden="true"></span>
            <p class="hero-rise hero-rise-2 mt-7 max-w-xl text-lg text-white/90 sm:text-xl">{{ $c('hero_sub') }}</p>
            @if(count($badges) > 1)
                <ul class="hero-rise hero-rise-3 mt-9 flex flex-wrap gap-x-8 gap-y-3">
                    @foreach(array_slice($badges, 1) as $b)<li class="inline-flex items-center gap-2 font-serif text-xl text-white">@icon('check', 'size-5 text-amber') {{ $b }}</li>@endforeach
                </ul>
            @endif
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
<section class="relative z-10 bg-white" aria-label="Why travel with us">
    <div class="container-page grid gap-y-8 py-9 sm:grid-cols-2 xl:grid-cols-4 lg:py-10">
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
<section class="bg-ivory py-16 sm:py-24" aria-labelledby="ht-about">
    <div class="container-page grid items-center gap-12 lg:grid-cols-[0.95fr_1.05fr] lg:gap-20">
        <div class="relative pb-10 pr-8 sm:pr-14">
            <div class="relative aspect-[4/5] overflow-hidden rounded-[2rem] shadow-card">
                <x-photo base="images/photos/flower-carpet" alt="Flower carpet laid out in front of the Brussels Grand Place" :widths="[480, 800, 1280, 1600]" sizes="(min-width:1024px) 520px, 80vw" fetchpriority="low" :ratio="[1600, 1023]" class="absolute inset-0 size-full" />
            </div>
            <div class="absolute bottom-0 right-0 aspect-square w-2/5 overflow-hidden rounded-3xl border-4 border-ivory shadow-lift">
                <x-photo base="images/photos/gravensteen" alt="The medieval Gravensteen castle in Ghent at sunset" :widths="[480, 800]" sizes="240px" fetchpriority="low" :ratio="[1600, 1044]" class="absolute inset-0 size-full" />
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
            <div class="mt-8"><x-button variant="primary" size="lg" icon="arrow-right" @click="$store.modal.open()">{{ $c('why_cta') ?: 'Plan My Journey' }}</x-button></div>
        </div>
    </div>
</section>

{{-- 4. CHOOSE YOUR JOURNEY (card layout copied from the client's reference): heading block, then a full-width, edge-to-edge row of three equal photo cards.
     Each card: title, one-line tagline, a small uppercase "Best for" line and an outlined square "Get quote" button, all bottom-left over the photo. --}}
@php
    $bestFor = ['self-drive' => 'Couples · Families · Independent travellers', 'chauffeur-driven' => 'Couples · Solo travellers · Luxury seekers', 'coach-tours' => 'Solo travellers · Couples · Groups'];
@endphp
<section class="bg-white pb-16 pt-16 sm:pb-24 sm:pt-24" aria-labelledby="ht-ways">
    <div class="container-page">
        <div class="mx-auto max-w-2xl text-center">
            @include('partials.ht-eyebrow', ['label' => $c('ways_eyebrow') ?: 'Choose your journey', 'center' => true])
            <h2 id="ht-ways" class="mt-5 font-serif text-heading">@heading($c('ways_heading') ?: 'Three Ways to *Travel* Belgium')</h2>
            <p class="mt-4 text-lg text-slate">{{ $c('ways_sub') ?: 'Choose how you move. We handle everything else.' }}</p>
        </div>
    </div>
    <div class="mt-12 grid gap-5 md:grid-cols-3">
        @foreach($tourTypes as $t)
            <article class="group relative isolate flex min-h-[26rem] flex-col justify-end overflow-hidden bg-ink p-6 text-white sm:min-h-[30rem] lg:min-h-[32rem] lg:p-8">
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
</section>
{{-- 5. PACKAGES: one swipeable carousel per tour type (3 cards per view on desktop, 2 on tablet, 1 on mobile), page indicator, no autoplay --}}
<section class="bg-ivory py-16 sm:py-24" aria-labelledby="ht-packages">
    <div class="container-page space-y-16">
        <div class="mx-auto max-w-2xl text-center">
            @include('partials.ht-eyebrow', ['label' => 'Signature journeys', 'center' => true])
            <h2 id="ht-packages" class="mt-5 font-serif text-heading">@heading('Trips for *Every* Traveller')</h2>
        </div>
        @foreach($tourTypes as $t)
            @if($t->packages->isNotEmpty())
                <div x-data="pageCarousel()" role="region" aria-roledescription="carousel" aria-label="{{ $t->name }} trips">
                    <div class="flex flex-wrap items-end justify-between gap-4 border-b border-ink/15 pb-4">
                        <h3 class="font-display text-3xl">{{ $t->name }}</h3>
                        <a href="{{ route('tours.show', $t->slug) }}" class="inline-flex min-h-11 items-center gap-2 rounded-full border-2 border-ink px-5 text-sm font-semibold hover:bg-ink hover:text-cream">View all @icon('arrow-right', 'size-4')</a>
                    </div>
                    <div x-ref="track" @scroll.passive="update()" @keydown.arrow-left.prevent="prev()" @keydown.arrow-right.prevent="next()" tabindex="0" aria-label="{{ $t->name }} packages, swipe or use the arrows"
                         class="relative mt-8 flex snap-x snap-mandatory items-stretch gap-6 overflow-x-auto scroll-smooth pb-4 [scrollbar-width:none] motion-reduce:scroll-auto [&::-webkit-scrollbar]:hidden">
                        @foreach($t->packages as $package)
                            <div class="flex w-full shrink-0 snap-start sm:w-[calc((100%-1.5rem)/2)] lg:w-[calc((100%-3rem)/3)] [&>*]:w-full" role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $loop->count }}">
                                @include('partials.package-card', ['package' => $package])
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination: round arrows either side of a row of pill segments (the current page stretches and turns amber), plus a counter --}}
                    <div x-show="pages > 1" x-cloak class="mt-6 flex items-center justify-center gap-4">
                        <button type="button" @click="prev()" :disabled="page === 0" aria-label="Previous page"
                                class="grid size-11 place-items-center rounded-full border-2 border-ink/25 text-ink transition hover:border-ink hover:bg-ink hover:text-cream disabled:pointer-events-none disabled:opacity-30">@icon('arrow-right', 'size-5 rotate-180')</button>
                        <div class="flex items-center gap-2" role="group" aria-label="Choose a page">
                            <template x-for="p in pages" :key="p">
                                <button type="button" @click="go(p - 1)" :aria-label="'Page ' + p" :aria-current="page === p - 1 ? 'true' : null"
                                        class="h-2 rounded-full transition-all duration-300" :class="page === p - 1 ? 'w-12 bg-amber' : 'w-2.5 bg-ink/20 hover:bg-ink/40'"></button>
                            </template>
                        </div>
                        <button type="button" @click="next()" :disabled="page >= pages - 1" aria-label="Next page"
                                class="grid size-11 place-items-center rounded-full border-2 border-ink/25 text-ink transition hover:border-ink hover:bg-ink hover:text-cream disabled:pointer-events-none disabled:opacity-30">@icon('arrow-right', 'size-5')</button>
                    </div>
                    <p x-show="pages > 1" x-cloak class="mt-2 text-center text-xs font-semibold uppercase tracking-[0.2em] text-slate" aria-live="polite"><span x-text="page + 1"></span> / <span x-text="pages"></span></p>
                </div>
            @endif
        @endforeach
    </div>
</section>
{{-- 6. WHY CHOOSE US: dusk band, photo plus a 2x2 grid of reasons --}}
@if($wcu->isNotEmpty())
<section class="on-dark bg-dusk py-16 text-cream sm:py-24" aria-labelledby="ht-wcu">
    <div class="container-page">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div class="max-w-2xl">
                @include('partials.ht-eyebrow', ['label' => $c('wcu_eyebrow') ?: 'Why choose us', 'dark' => true])
                <h2 id="ht-wcu" class="mt-5 font-serif text-heading !text-cream">@heading($c('wcu_heading') ?: 'Why Choose *Us*')</h2>
                @if($c('wcu_sub'))<p class="mt-4 text-lg text-cream/85">{{ $c('wcu_sub') }}</p>@endif
            </div>
            <x-button variant="primary" size="lg" icon="arrow-right" @click="$store.modal.open()">Start Your Journey</x-button>
        </div>
        <div class="mt-12 grid gap-6 lg:grid-cols-[1fr_1.1fr]">
            <div class="relative min-h-[20rem] overflow-hidden rounded-3xl">
                <x-photo base="images/photos/gravensteen" alt="" :widths="[480, 800, 1280]" sizes="(min-width:1024px) 45vw, 92vw" fetchpriority="low" :ratio="[1600, 1044]" class="absolute inset-0 size-full" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach($wcu as $f)
                    <div class="rounded-3xl bg-white/10 p-6 ring-1 ring-white/15">
                        <span class="grid size-12 place-items-center rounded-full bg-amber text-ink">@icon($f['icon'], 'size-6')</span>
                        <h3 class="mt-4 font-display text-xl text-cream">{{ $f['title'] }}</h3>
                        <p class="mt-2 text-[0.95rem] text-cream/85">{{ $f['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

{{-- 7. HOW IT WORKS: three numbered cards --}}
@if($steps->isNotEmpty())
<section class="bg-white py-16 sm:py-24" aria-labelledby="ht-steps">
    <div class="container-page">
        <div class="mx-auto max-w-2xl text-center">
            @include('partials.ht-eyebrow', ['label' => $c('steps_eyebrow') ?: 'How it works', 'center' => true])
            <h2 id="ht-steps" class="mt-5 font-serif text-heading">@heading($c('steps_heading') ?: 'Planning Made *Simple*')</h2>
            @if($c('steps_sub'))<p class="mt-4 text-lg text-slate">{{ $c('steps_sub') }}</p>@endif
        </div>
        <ol class="mt-12 grid gap-6 md:grid-cols-3">
            @foreach($steps as $s)
                <li class="rounded-3xl border border-ink/10 bg-cream p-7">
                    <span class="grid size-12 place-items-center rounded-full bg-amber font-display text-xl text-ink">{{ $loop->iteration }}</span>
                    <h3 class="mt-5 font-display text-2xl">{{ $s['title'] }}</h3>
                    <p class="mt-2 text-slate">{{ $s['text'] }}</p>
                </li>
            @endforeach
        </ol>
        <div class="mt-10 text-center"><x-button variant="primary" size="lg" icon="arrow-right" @click="$store.modal.open()">{{ $c('steps_cta') ?: 'Get a Free Quote' }}</x-button></div>
    </div>
</section>
@endif

{{-- 8. TESTIMONIALS: three glass cards on charcoal --}}
@if($quotes->isNotEmpty())
<section class="on-dark bg-ink py-16 text-cream sm:py-24" aria-labelledby="ht-quotes">
    <div class="container-page">
        <div class="max-w-2xl">
            @include('partials.ht-eyebrow', ['label' => $c('testimonials_eyebrow') ?: 'Traveller words', 'dark' => true])
            <h2 id="ht-quotes" class="mt-5 font-serif text-heading !text-cream">@heading($c('testimonials_heading') ?: 'Stories Worth *Retelling*')</h2>
        </div>
        <div class="mt-12 grid gap-6 md:grid-cols-3">
            @foreach($quotes as $t)
                <figure class="flex flex-col rounded-3xl bg-white/5 p-7 ring-1 ring-white/15">
                    <svg viewBox="0 0 24 24" class="size-8 text-amber" fill="currentColor" aria-hidden="true"><path d="M7 7h4v4c0 3-1.6 5-4.5 5.5v-2C8 14 8.6 13 8.6 12H7zm8 0h4v4c0 3-1.6 5-4.5 5.5v-2C16 14 16.6 13 16.6 12H15z"/></svg>
                    <blockquote class="mt-4 flex-1 text-lg leading-relaxed text-cream/90">{{ $t->quote }}</blockquote>
                    <figcaption class="mt-6 flex items-center gap-3 border-t border-white/15 pt-5">
                        @if($t->photo)
                            <span class="relative size-12 overflow-hidden rounded-full"><x-photo :base="$t->photo" :alt="$t->photo_alt ?: ''" :widths="[480, 800]" sizes="48px" :ratio="[480, 480]" class="size-full" /></span>
                        @else
                            <span class="grid size-12 place-items-center rounded-full bg-sky text-ink/70" aria-hidden="true"><svg viewBox="0 0 24 24" class="size-7" fill="currentColor"><circle cx="12" cy="8.5" r="4"/><path d="M3.5 24c0-5 3.8-8 8.5-8s8.5 3 8.5 8z"/></svg></span>
                        @endif
                        <div><p class="font-semibold">{{ $t->name }}</p>@if($t->location)<p class="text-xs uppercase tracking-[0.16em] text-cream/70">{{ $t->location }}</p>@endif</div>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- 9. DESTINATIONS: photo grid, tour counts come from the database --}}
<section class="bg-ivory py-16 sm:py-24" aria-labelledby="ht-dest">
    <div class="container-page">
        <div class="mx-auto max-w-2xl text-center">
            @include('partials.ht-eyebrow', ['label' => $c('dest_eyebrow') ?: 'Top destinations', 'center' => true])
            <h2 id="ht-dest" class="mt-5 font-serif text-heading">@heading('Discover the *Best of Belgium*')</h2>
        </div>
        <div class="mt-12 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-3">
            @foreach($experiences as $e)
                <a href="{{ route('packages.index', ['experience' => $e->slug]) }}" class="group relative isolate flex aspect-[4/3] items-end overflow-hidden rounded-3xl bg-ink p-4 text-white sm:p-6 {{ $loop->first ? 'sm:aspect-[4/3]' : '' }}">
                    @if($e->image)<x-photo :base="$e->image" :alt="$e->image_alt ?: ''" :widths="[480, 800, 1280]" sizes="(min-width:1024px) 33vw, 50vw" fetchpriority="low" :ratio="[1600, 1067]" class="absolute inset-0 -z-20 size-full transition-transform duration-700 group-hover:scale-105" style="object-position: {{ $e->image_focus ?: '50% 50%' }}" />@endif
                    <span class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/85 via-ink/20 to-transparent"></span>
                    <span class="flex w-full items-end justify-between gap-3">
                        <span><span class="block font-display text-2xl sm:text-3xl">{{ $e->name }}</span><span class="text-sm text-white/85">{{ $nTours($e->packages_count) }}</span></span>
                        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-aqua text-ink transition group-hover:bg-amber">@icon('arrow-right', 'size-4')</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- 10. FAQ: heading and help card on the left, native accordion on the right --}}
@if($faqs->isNotEmpty())
<section class="bg-white py-16 sm:py-24" aria-labelledby="ht-faq">
    <div class="container-page grid gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:gap-16">
        <div>
            @include('partials.ht-eyebrow', ['label' => $c('faq_eyebrow') ?: 'Questions'])
            <h2 id="ht-faq" class="mt-5 font-serif text-heading">@heading($c('faq_heading') ?: 'Good to *Know*')</h2>
            <div class="mt-8 rounded-3xl bg-dusk p-7 text-cream">
                <h3 class="font-display text-2xl text-cream">Still have questions?</h3>
                <p class="mt-2 text-cream/85">Ask us anything. We reply quickly and there is no obligation.</p>
                <div class="mt-5 flex flex-wrap gap-3">
                    @if($site->phone)<x-button :href="'tel:'.$site->phone_href" variant="light" size="sm">Call us</x-button>@endif
                    <x-button variant="primary" size="sm" @click="$store.modal.open()">Get Quote Now</x-button>
                </div>
            </div>
        </div>
        <div class="space-y-3">
            @foreach($faqs->take(6) as $f)
                <details name="ht-faq" class="group rounded-2xl border border-amber/25 bg-white open:border-amber open:bg-cream/50">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-5 font-display text-lg [&::-webkit-details-marker]:hidden">{{ $f->question }} @icon('chevron-down', 'size-5 shrink-0 text-slate transition-transform group-open:rotate-180')</summary>
                    <p class="px-5 pb-5 text-slate">{{ $f->answer }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- 11. JOURNAL --}}
@include('partials.blog-section')

@endsection
