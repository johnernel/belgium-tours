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
        <x-photo base="images/photos/bruges-canal" alt="" sizes="100vw" :eager="true" class="hero-slide hero-slide-1 absolute inset-0 size-full" />
        <x-photo base="images/photos/brussels-grand-place" alt="" sizes="100vw" fetchpriority="low" class="hero-slide hero-slide-2 absolute inset-0 size-full" />
        <x-photo base="images/photos/gravensteen" alt="" sizes="100vw" fetchpriority="low" class="hero-slide hero-slide-3 absolute inset-0 size-full" />
    </div>
    <div class="absolute inset-0 z-[1] bg-gradient-to-b from-ink/55 via-ink/22 to-ink/58"></div>
    <div class="absolute inset-0 z-[1] bg-gradient-to-r from-ink/76 via-ink/38 to-ink/6"></div>

    <div class="container-page relative z-10 grid w-full flex-1 items-end gap-10 pb-12 pt-[calc(4.5rem+2.5rem)] lg:grid-cols-[1.15fr_0.85fr] lg:items-center lg:gap-14 lg:pb-14 lg:pt-[calc(8rem+1.5rem)]">
        <div class="lg:pt-24 max-sm:text-center">
            <p class="hero-rise inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-medium text-ink shadow-card">@icon('star', 'size-4 text-amber-deep') Authentic Trips, Belgian Heart</p>
            <h1 class="on-dark hero-rise mt-6 [text-shadow:0_1px_14px_rgb(43_46_55/0.6)] font-serif text-[2.65rem] font-medium leading-[1.05] !text-white sm:text-6xl lg:text-[4.25rem]">
                {{ $hl1 }}<br><span class="text-amber">{{ $hl2 }}</span>
            </h1>
            <span class="hero-rise mt-4 block h-0.5 w-24 bg-amber max-sm:mx-auto" aria-hidden="true"></span>
            <p class="hero-rise hero-rise-2 mt-7 max-w-xl text-lg text-white [text-shadow:0_1px_14px_rgb(43_46_55/0.6)] max-sm:hidden sm:text-xl">{{ $c('hero_sub') }}</p>

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
<section class="bg-ivory py-16 max-md:hidden sm:py-24" aria-labelledby="ht-about">
    <div class="container-page grid items-center gap-12 lg:grid-cols-[0.95fr_1.05fr] lg:gap-20">
        <div class="relative pb-10 pr-8 sm:pr-14">
            <div class="relative aspect-[4/5] overflow-hidden shadow-card">
                <x-photo base="images/photos/flower-carpet" alt="Flower carpet laid out in front of the Brussels Grand Place" :widths="[480, 800, 1280, 1600]" sizes="(min-width:1024px) 520px, 80vw" fetchpriority="low" :ratio="[1600, 1023]" class="absolute inset-0 size-full" />
            </div>
            <div class="absolute bottom-0 right-0 aspect-square w-2/5 overflow-hidden border-4 border-ivory shadow-lift">
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
            <div class="mt-8"><x-button square variant="cta" size="lg" icon="arrow-right" @click="$store.modal.open()">{{ $c('why_cta') ?: 'Plan My Journey' }}</x-button></div>
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
            <div class="flex items-center gap-2" role="group" aria-label="Choose a card">
                <template x-for="p in pages" :key="p">
                    <button type="button" @click="go(p - 1)" :aria-label="'Card ' + p" :aria-current="page === p - 1 ? 'true' : null" class="h-2 rounded-full transition-all duration-300" :class="page === p - 1 ? 'w-12 bg-amber' : 'w-2.5 bg-ink/20 hover:bg-ink/40'"></button>
                </template>
            </div>
            <button type="button" @click="next()" :disabled="page >= pages - 1" aria-label="Next card" class="grid size-11 place-items-center rounded-full border-2 border-ink/25 text-ink transition hover:border-ink hover:bg-ink hover:text-cream disabled:pointer-events-none disabled:opacity-30">@icon('arrow-right', 'size-5')</button>
        </div>
        <p class="mt-2 text-center text-xs font-semibold uppercase tracking-[0.2em] text-slate" aria-live="polite"><span x-text="page + 1"></span> / <span x-text="pages"></span></p>
    </div>
    </div>
</section>
{{-- 5. PACKAGES: one swipeable carousel per tour type (3 cards per view on desktop, 2 on tablet, 1 on mobile), page indicator, no autoplay --}}
<section class="bg-ivory py-16 sm:py-24" aria-labelledby="ht-packages">
    <div class="container-page space-y-16">
        <div class="mx-auto max-w-2xl text-center">
            @include('partials.ht-eyebrow', ['label' => 'Our Packages', 'center' => true])
            <h2 id="ht-packages" class="mt-5 font-serif text-heading">@heading('Trips for *Every* Traveller')</h2>
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
                    {{-- Phones only: the "View all" link moves from the heading row to below the carousel --}}
                    <a href="{{ route('tours.show', $t->slug) }}" class="mx-auto mt-5 flex min-h-10 w-fit items-center justify-center gap-2 border-2 border-ink px-5 text-xs font-semibold uppercase tracking-[0.12em] hover:bg-ink hover:text-cream sm:hidden">View all packages @icon('arrow-right', 'size-4')<span class="sr-only"> {{ $t->name }}</span></a>
                </div>
            @endif
        @endforeach
    </div>
</section>
{{-- 6. WHY CHOOSE US: dusk band, photo plus a 2x2 grid of reasons --}}
@if($wcu->isNotEmpty())
<section class="relative isolate overflow-hidden bg-mist py-16 text-ink sm:py-24" aria-labelledby="ht-wcu">
    {{-- Decorative Antwerp skyline (client-supplied image turned into a soft tint), bottom right, behind everything; hidden on phones --}}
    <img src="{{ asset('images/decor/antwerp-skyline.webp') }}" alt="" aria-hidden="true" width="927" height="617" loading="lazy" class="pointer-events-none absolute bottom-0 right-0 -z-10 hidden h-[24rem] w-auto max-w-[60%] select-none opacity-60 md:block lg:h-[28rem]">
    <div class="container-page">
        <div class="flex flex-wrap items-end justify-between gap-6 max-md:justify-center max-md:text-center">
            <div class="max-w-2xl">
                @include('partials.ht-eyebrow', ['label' => $c('wcu_eyebrow') ?: 'Why choose us', 'mobileCenter' => true])
                <h2 id="ht-wcu" class="mt-5 font-serif text-heading">@heading($c('wcu_heading') ?: 'Why Choose *Us*')</h2>
                @if($c('wcu_sub'))<p class="mt-4 text-lg text-ink/85">{{ $c('wcu_sub') }}</p>@endif
            </div>
            <x-button square variant="primary" size="lg" icon="arrow-right" @click="$store.modal.open()">Start Your Journey</x-button>
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
                        <div class="border border-mist-deep bg-mist p-6 shadow-[0_8px_22px_-14px_var(--color-mist-deep)] max-sm:w-[calc(100%-2rem)] max-sm:shrink-0 max-sm:snap-start">
                            <span class="grid size-12 place-items-center rounded-full bg-mint text-teal-deep">@if(str_starts_with($f['icon'], 'svg:'))<span class="qicon size-7" style="--qi: url({{ asset('images/why-icons/'.substr($f['icon'], 4).'.svg') }})" aria-hidden="true"></span>@else @icon($f['icon'], 'size-6')@endif</span>
                            <h3 class="mt-4 font-display text-xl text-ink">{{ $f['title'] }}</h3>
                            <p class="mt-2 text-[0.95rem] text-ink/80">{{ $f['text'] }}</p>
                        </div>
                    @endforeach
                </div>
                <div x-show="pages > 1" x-cloak class="mt-5 flex items-center justify-center gap-2 sm:hidden" role="group" aria-label="Choose a card">
                    <template x-for="p in pages" :key="p">
                        <button type="button" @click="go(p - 1)" :aria-label="'Card ' + p" :aria-current="page === p - 1 ? 'true' : null" class="h-2 rounded-full transition-all duration-300" :class="page === p - 1 ? 'w-12 bg-amber' : 'w-2.5 bg-ink/20 hover:bg-ink/40'"></button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- 7. STRESS-FREE TRAVEL (layout copied from the client's reference, wording rewritten for Belgium): centred label, heading and one-line intro, then three equal square-cornered cards, each with a round icon, a bold title and a centred description --}}
@php
    $calm = [
        ['users', 'Every Detail. Already Handled.', 'Our Belgium specialists plan every detail: hand-picked stays, ideal pacing and local stops, so nothing is left to chance.'],
        ['search', 'Local access. Real moments.', 'Our local connections open doors beyond the tourist routes: insider tips, quiet corners and the true heart of Belgium, hassle-free.'],
        ['compass', 'Travel Your Way', 'Take the wheel on a self-drive adventure, or relax with a private chauffeur or coach tour. Every journey is seamless.'],
    ];
@endphp
<section class="bg-white py-16 sm:py-24" aria-labelledby="ht-steps">
    <div class="container-page">
        <div class="mx-auto max-w-4xl text-center">
            @include('partials.ht-eyebrow', ['label' => 'Stress-free travel', 'center' => true])
            <h2 id="ht-steps" class="mt-5 font-serif text-heading">@heading('Discover Belgium the *Stress-Free* Way')</h2>
            <p class="mx-auto mt-5 max-w-3xl text-lg text-ink">We take care of every detail so you can simply show up, enjoy the canals and cobbled squares, and let Belgium do the rest.</p>
        </div>
        {{-- Phones and small tablets (below md): a swipeable scroll-snap carousel, one card at a time with the next card peeking 20px in from the right, and pill dots like the package carousels; from md up the 3 equal columns are unchanged --}}
        <div class="mt-14 min-w-0" x-data="pageCarousel()">
            <div x-ref="track" @scroll.passive="update()" tabindex="0" aria-label="Stress-free travel, swipe to see more"
                 class="relative grid gap-6 md:grid-cols-3 lg:gap-8 max-md:flex max-md:snap-x max-md:snap-mandatory max-md:gap-3 max-md:overflow-x-auto max-md:scroll-smooth max-md:[scrollbar-width:none] max-md:after:w-5 max-md:after:shrink-0 max-md:after:content-[''] max-md:motion-reduce:scroll-auto max-md:[&::-webkit-scrollbar]:hidden">
                @foreach($calm as [$ic, $title, $text])
                    <article class="flex flex-col items-center bg-slate px-8 pb-14 pt-11 text-center text-white max-md:w-[calc(100%-2rem)] max-md:shrink-0 max-md:snap-start">
                        <span class="grid size-[4.75rem] place-items-center rounded-full bg-white/15 text-aqua-light">@icon($ic, 'size-9')</span>
                        <h3 class="mt-7 font-display text-xl leading-snug text-white lg:text-[1.35rem]">{{ $title }}</h3>
                        <p class="mt-4 text-lg leading-relaxed text-white/90">{{ $text }}</p>
                    </article>
                @endforeach
            </div>
            <div x-show="pages > 1" x-cloak class="mt-6 flex items-center justify-center gap-2 md:hidden" role="group" aria-label="Choose a card">
                <template x-for="p in pages" :key="p">
                    <button type="button" @click="go(p - 1)" :aria-label="'Card ' + p" :aria-current="page === p - 1 ? 'true' : null" class="h-2 rounded-full transition-all duration-300" :class="page === p - 1 ? 'w-12 bg-amber' : 'w-2.5 bg-ink/20 hover:bg-ink/40'"></button>
                </template>
            </div>
        </div>    </div>
</section>
{{-- 8. TESTIMONIALS (layout copied from the client's reference): a full-width video block with a play button and a fade into the dark section, the centred label and heading,
     then the testimonials as a carousel (3 cards per view on desktop, 2 tablet, 1 mobile; arrows, dots, swipe, no autoplay). No star ratings: there are none to back them. --}}
@if($testimonials->isNotEmpty())
<section class="on-dark bg-ink text-cream" aria-labelledby="ht-quotes">
    {{-- Video block: starts by itself (muted, looping) when the section scrolls into view and pauses when it leaves; the button pauses or resumes it. Reduced-motion and data-saver visitors get the poster and the play button instead. --}}
    <div class="relative h-[17rem] overflow-hidden sm:h-[26rem] lg:h-[32rem]" x-data="{ playing: false, userPaused: false }"
         x-init="const v = $refs.v; const calm = matchMedia('(prefers-reduced-motion: reduce)').matches || (navigator.connection && navigator.connection.saveData);
                 if (! calm && 'IntersectionObserver' in window) {
                     new IntersectionObserver(([e]) => {
                         if (e.isIntersecting) { if (! userPaused) { v.play().then(() => playing = true).catch(() => {}); } }
                         else { v.pause(); playing = false; }
                     }, { threshold: 0.35 }).observe($el);
                 }">
        <video x-ref="v" class="absolute inset-0 size-full object-cover" muted loop playsinline preload="none" tabindex="-1" aria-label="Belgium travel footage"
               poster="{{ asset('media/ways-poster.webp').'?v='.filemtime(public_path('media/ways-poster.webp')) }}">
            <source src="{{ asset('media/ways-loop.webm').'?v='.filemtime(public_path('media/ways-loop.webm')) }}" type="video/webm">
            <source src="{{ asset('media/ways-loop.mp4').'?v='.filemtime(public_path('media/ways-loop.mp4')) }}" type="video/mp4">
        </video>
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-ink via-ink/25 to-ink/30"></div>
        <button type="button" @click="playing ? ($refs.v.pause(), playing = false, userPaused = true) : ($refs.v.play(), playing = true, userPaused = false)" :aria-label="playing ? 'Pause video' : 'Play video'" :aria-pressed="playing"
                class="absolute left-1/2 top-[42%] grid size-20 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-ink shadow-lift transition duration-300 hover:scale-105 hover:bg-white focus-visible:opacity-100 sm:size-24"
                :class="playing ? 'opacity-0 hover:opacity-100' : 'opacity-100'">
            <svg x-show="!playing" viewBox="0 0 24 24" class="ml-1 size-9" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13a1 1 0 0 0 1.5.86l10.5-6.5a1 1 0 0 0 0-1.72L9.5 4.64A1 1 0 0 0 8 5.5Z"/></svg>
            <svg x-show="playing" x-cloak viewBox="0 0 24 24" class="size-9" fill="currentColor" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
        </button>
    </div>

    <div class="container-page relative pb-16 sm:pb-24">
        <div class="-mt-10 text-center sm:-mt-14">
            @include('partials.ht-eyebrow', ['label' => $c('testimonials_eyebrow') ?: 'Testimonials', 'center' => true, 'dark' => true])
            <h2 id="ht-quotes" class="mt-5 font-serif text-heading !text-cream">@heading($c('testimonials_heading') ?: 'What Our Guests *Say*')</h2>
        </div>

        <div class="mt-12" x-data="pageCarousel()" role="region" aria-roledescription="carousel" aria-label="Traveller testimonials">
            <div x-ref="track" @scroll.passive="update()" @keydown.arrow-left.prevent="prev()" @keydown.arrow-right.prevent="next()" tabindex="0" aria-label="Testimonials, swipe or use the arrows"
                 class="relative flex snap-x snap-mandatory items-stretch gap-3 overflow-x-auto scroll-smooth pb-2 [scrollbar-width:none] motion-reduce:scroll-auto max-sm:after:w-5 max-sm:after:shrink-0 max-sm:after:content-[''] sm:gap-6 [&::-webkit-scrollbar]:hidden">
                @foreach($testimonials as $t)
                    <figure class="flex w-[calc(100%-2rem)] shrink-0 snap-start flex-col rounded-md bg-white/5 p-7 ring-1 ring-white/15 sm:w-[calc((100%-1.5rem)/2)] lg:w-[calc((100%-3rem)/3)]" role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $loop->count }}">
                        <div class="flex items-start justify-between gap-4">
                            <svg viewBox="0 0 24 24" class="size-10 text-amber" fill="currentColor" aria-hidden="true"><path d="M7 7h4v4c0 3-1.6 5-4.5 5.5v-2C8 14 8.6 13 8.6 12H7zm8 0h4v4c0 3-1.6 5-4.5 5.5v-2C16 14 16.6 13 16.6 12H15z"/></svg>
                            @if($t->photo)
                                <span class="relative size-12 shrink-0 overflow-hidden rounded-full"><x-photo :base="$t->photo" :alt="$t->photo_alt ?: ''" :widths="[480, 800]" sizes="48px" :ratio="[480, 480]" class="size-full" /></span>
                            @else
                                <span class="grid size-12 shrink-0 place-items-center rounded-full bg-sky text-ink/70" aria-hidden="true"><svg viewBox="0 0 24 24" class="size-7" fill="currentColor"><circle cx="12" cy="8.5" r="4"/><path d="M3.5 24c0-5 3.8-8 8.5-8s8.5 3 8.5 8z"/></svg></span>
                            @endif
                        </div>
                        <blockquote class="mt-5 flex-1 text-[0.98rem] leading-relaxed text-cream/90">{{ $t->quote }}</blockquote>
                        <figcaption class="mt-6 border-t border-white/15 pt-4"><p class="font-semibold">{{ $t->name }}</p>@if($t->location)<p class="mt-0.5 text-xs uppercase tracking-[0.16em] text-cream/70">{{ $t->location }}</p>@endif</figcaption>
                    </figure>
                @endforeach
            </div>

            <div x-show="pages > 1" x-cloak class="mt-8 flex items-center justify-center gap-4">
                <button type="button" @click="prev()" :disabled="page === 0" aria-label="Previous testimonials" class="grid size-11 place-items-center rounded-full border-2 border-white/30 text-cream transition hover:border-amber hover:text-amber disabled:pointer-events-none disabled:opacity-30">@icon('arrow-right', 'size-5 rotate-180')</button>
                <div class="flex items-center gap-2" role="group" aria-label="Choose a page">
                    <template x-for="p in pages" :key="p">
                        <button type="button" @click="go(p - 1)" :aria-label="'Page ' + p" :aria-current="page === p - 1 ? 'true' : null" class="h-2 rounded-full transition-all duration-300" :class="page === p - 1 ? 'w-12 bg-amber' : 'w-2.5 bg-white/30 hover:bg-white/60'"></button>
                    </template>
                </div>
                <button type="button" @click="next()" :disabled="page >= pages - 1" aria-label="Next testimonials" class="grid size-11 place-items-center rounded-full border-2 border-white/30 text-cream transition hover:border-amber hover:text-amber disabled:pointer-events-none disabled:opacity-30">@icon('arrow-right', 'size-5')</button>
            </div>
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
        <div x-show="pages > 1" x-cloak class="mt-6 flex items-center justify-center gap-2 sm:hidden" role="group" aria-label="Choose a destination">
            <template x-for="p in pages" :key="p">
                <button type="button" @click="go(p - 1)" :aria-label="'Destination ' + p" :aria-current="page === p - 1 ? 'true' : null" class="h-2 rounded-full transition-all duration-300" :class="page === p - 1 ? 'w-12 bg-amber' : 'w-2.5 bg-ink/20 hover:bg-ink/40'"></button>
            </template>
        </div>
        </div>
    </div>
</section>

{{-- 9b. FIND YOUR STYLE: simple, secure payments (layout copied from the client's reference, wording rewritten for Belgium): centred label, heading and two-line intro,
     four equal square cards (round dark icon, two-line title, centred text), one centred button underneath. The payment wording is placeholder copy until the client confirms how payments work. --}}
@php
    $pay = [
        ['smartphone', 'images/photos/countryside', 'No More Payment Headaches', 'Let each traveller pay securely online, with no more chasing or managing group payments.'],
        ['shield', 'images/photos/liege-guillemins', 'Secure & Convenient', 'Fast, safe and hassle-free transactions so you can book with complete confidence.'],
        ['bookmark', 'images/photos/bruges-belfry', 'Instant Booking Confirmation', 'Once your group is finalised, booking is confirmed immediately, with no back and forth.'],
        ['banknote', 'images/photos/bruges-market', 'Flexible Payment Options', 'Multiple secure payment options available to suit every traveller in your whole group.'],
    ];
@endphp
<section class="bg-white py-16 sm:py-24" aria-labelledby="ht-pay">
    <div class="container-page">
        <div class="mx-auto max-w-4xl text-center">
            @include('partials.ht-eyebrow', ['label' => 'Find your style', 'center' => true])
            <h2 id="ht-pay" class="mt-5 font-serif text-heading">@heading('Simple, Secure Payments, No *Worries Ever*')</h2>
            <p class="mx-auto mt-5 max-w-3xl text-lg text-ink">Whether you are travelling with friends, family or colleagues, our flexible group payment options make booking your Belgium trip simple, straightforward and completely hassle-free for everyone involved.</p>
        </div>
        {{-- Phones (below sm): a swipeable scroll-snap carousel, one card at a time with the next card peeking 20px in from the right, and pill dots like the package carousels; from sm up the 2 / 4 column grid is unchanged --}}
        <div class="mt-12 min-w-0" x-data="pageCarousel()">
        <div x-ref="track" @scroll.passive="update()" tabindex="0" aria-label="Payment options, swipe to see more" class="relative grid gap-6 sm:grid-cols-2 xl:grid-cols-4 xl:gap-8 max-sm:flex max-sm:snap-x max-sm:snap-mandatory max-sm:gap-3 max-sm:overflow-x-auto max-sm:scroll-smooth max-sm:pb-6 max-sm:[scrollbar-width:none] max-sm:after:w-5 max-sm:after:shrink-0 max-sm:after:content-['] max-sm:motion-reduce:scroll-auto max-sm:[&::-webkit-scrollbar]:hidden">
            @foreach($pay as [$ic, $img, $title, $text])
                {{-- Card copied from the client's reference: rounded corners and a big soft shadow, a white round icon with an amber ring, serif title, centred text, and a faded photo that fades in from the middle of the card down. The white wash over the photo keeps the text readable. --}}
                <article class="relative isolate flex min-h-[22rem] flex-col items-center overflow-hidden rounded-lg bg-white px-5 pb-10 pt-9 text-center shadow-lift max-sm:w-[calc(100%-2rem)] max-sm:shrink-0 max-sm:snap-start xl:px-4">
                    <div class="absolute inset-x-0 bottom-0 -z-20 h-[80%] opacity-35 [mask-image:linear-gradient(to_bottom,transparent,black_45%)]" aria-hidden="true">
                        <x-photo :base="$img" alt="" :widths="[480, 800]" sizes="(min-width:1024px) 25vw, 90vw" fetchpriority="low" :ratio="[1600, 1067]" class="size-full" />
                    </div>
                    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-white via-white/75 to-white/80" aria-hidden="true"></div>
                    <span class="grid size-20 place-items-center rounded-full border-2 border-amber bg-white text-slate shadow-card [&_svg]:![stroke-width:1.25]">@icon($ic, 'size-9')</span>
                    <h3 class="mt-7 max-w-[12rem] font-display text-[1.4rem] font-semibold leading-tight text-ink">{{ $title }}</h3>
                    <p class="mt-5 line-clamp-3 text-[0.97rem] font-medium leading-relaxed text-ink xl:text-[0.92rem]">{{ $text }}</p>
                </article>
            @endforeach
        </div>
        <div x-show="pages > 1" x-cloak class="mt-1 flex items-center justify-center gap-2 sm:hidden" role="group" aria-label="Choose a payment option">
            <template x-for="p in pages" :key="p">
                <button type="button" @click="go(p - 1)" :aria-label="'Option ' + p" :aria-current="page === p - 1 ? 'true' : null" class="h-2 rounded-full transition-all duration-300" :class="page === p - 1 ? 'w-12 bg-amber' : 'w-2.5 bg-ink/20 hover:bg-ink/40'"></button>
            </template>
        </div>
        </div>
        <div class="mt-12 text-center"><x-button square variant="cta" size="lg" @click="$store.modal.open()">Get Your Free Quote</x-button></div>
    </div>
</section>
{{-- 10. PLAN YOUR TRIP (layout copied from the client's reference): pale band, centred label, heading, intro, three small reassurance points, one button,
     and a faded skyline silhouette along the bottom edge (Belgian step gables and belfries instead of mountains) --}}
<section class="relative isolate overflow-hidden bg-white pb-44 pt-16 sm:pb-52 sm:pt-24" aria-labelledby="ht-plan">
    <div class="absolute inset-0 -z-20 bg-sky-soft/55" aria-hidden="true"></div>
    <div class="container-page text-center">
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
