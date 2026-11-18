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

{{-- 1. HERO: photo slideshow, headline on the left, the quiz on the right --}}
<section class="relative isolate -mt-[4.5rem] flex min-h-[44rem] flex-col overflow-hidden bg-ink text-white lg:-mt-[7rem] lg:min-h-[50rem]">
    <div class="absolute inset-0 z-0 overflow-hidden bg-ink" aria-hidden="true">
        <x-photo base="images/photos/bruges-canal" alt="" sizes="100vw" :eager="true" class="hero-slide hero-slide-1 absolute inset-0 size-full" />
        <x-photo base="images/photos/brussels-grand-place" alt="" sizes="100vw" fetchpriority="low" class="hero-slide hero-slide-2 absolute inset-0 size-full" />
        <x-photo base="images/photos/gravensteen" alt="" sizes="100vw" fetchpriority="low" class="hero-slide hero-slide-3 absolute inset-0 size-full" />
    </div>
    <div class="absolute inset-0 z-[1] bg-gradient-to-b from-ink/60 via-ink/35 to-ink/85"></div>
    <div class="absolute inset-0 z-[1] bg-gradient-to-r from-ink/75 via-ink/30 to-transparent"></div>

    <div class="container-page relative z-10 grid w-full flex-1 items-center gap-10 pb-16 pt-[calc(4.5rem+2.5rem)] lg:grid-cols-[1.1fr_0.9fr] lg:gap-14 lg:pb-24 lg:pt-[calc(7rem+2rem)]">
        <div>
            @if($badges)
                <p class="hero-rise inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-1.5 text-sm font-semibold text-white ring-1 ring-white/30 backdrop-blur">@icon('star', 'size-4 text-amber') {{ $badges[0] }}</p>
            @endif
            <h1 class="on-dark hero-rise mt-5 max-w-[16ch] font-serif text-5xl font-medium leading-[1.02] !text-white sm:text-6xl lg:max-w-[14ch] lg:text-[4.5rem]">@heading($headline)</h1>
            <p class="hero-rise hero-rise-2 mt-7 max-w-lg text-lg text-white/90 sm:text-xl">{{ $c('hero_sub') }}</p>
            <div class="hero-rise hero-rise-2 mt-8 flex flex-wrap gap-3">
                <x-button variant="primary" size="lg" icon="arrow-right" @click="$store.modal.open()">Get a Free Quote</x-button>
                <x-button :href="route('packages.index')" variant="light" size="lg">See Packages</x-button>
            </div>
            @if(count($badges) > 1)
                <ul class="hero-rise hero-rise-3 mt-8 flex flex-wrap gap-x-6 gap-y-2 text-sm font-semibold text-white/90">
                    @foreach(array_slice($badges, 1) as $b)<li class="inline-flex items-center gap-2">@icon('check', 'size-4 text-amber') {{ $b }}</li>@endforeach
                </ul>
            @endif
        </div>
        <div class="hero-rise hero-rise-2 lg:w-full lg:max-w-[28rem] lg:justify-self-end">
            @include('partials.quiz')
        </div>
    </div>
</section>

{{-- 2. TRUST STRIP: four reassurance points on charcoal, overlapping the hero edge --}}
<section class="relative z-10 bg-ink text-cream" aria-label="Why travellers trust us">
    <div class="container-page grid grid-cols-2 gap-y-8 py-9 lg:grid-cols-4">
        @foreach($trust as [$ic, $text])
            <div class="flex flex-col items-center gap-3 px-4 text-center {{ $loop->first ? '' : 'lg:border-l lg:border-cream/15' }}">
                <span class="grid size-12 place-items-center rounded-full border-2 {{ ['border-amber text-amber', 'border-aqua text-aqua'][$loop->index % 2] }}">@icon($ic, 'size-5')</span>
                <p class="max-w-[14rem] text-sm font-semibold leading-snug sm:text-base">{{ $text }}</p>
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

{{-- 4. CHOOSE YOUR JOURNEY: one tall photo card per tour type --}}
<section class="bg-white py-16 sm:py-24" aria-labelledby="ht-ways">
    <div class="container-page">
        <div class="mx-auto max-w-2xl text-center">
            @include('partials.ht-eyebrow', ['label' => $c('ways_eyebrow') ?: 'Choose your journey', 'center' => true])
            <h2 id="ht-ways" class="mt-5 font-serif text-heading">@heading($c('ways_heading') ?: 'Three Ways to *Travel* Belgium')</h2>
            <p class="mt-4 text-lg text-slate">{{ $c('ways_sub') ?: 'Choose how you move. We handle everything else.' }}</p>
        </div>
        <div class="mt-12 grid gap-6 md:grid-cols-3">
            @foreach($tourTypes as $t)
                <article class="group relative isolate flex min-h-[26rem] flex-col justify-end overflow-hidden rounded-3xl bg-ink p-6 text-white shadow-card sm:min-h-[30rem]">
                    @if($t->image)<x-photo :base="$t->image" alt="" :widths="[480, 800, 1280]" sizes="(min-width:768px) 33vw, 92vw" fetchpriority="low" :ratio="[1600, 1067]" class="absolute inset-0 -z-20 size-full transition-transform duration-700 group-hover:scale-105" />@endif
                    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/90 via-ink/35 to-transparent"></div>
                    <h3 class="font-display text-3xl text-white">{{ $t->name }}</h3>
                    @if($t->summary)<p class="mt-2 text-white/85">{{ \Illuminate\Support\Str::limit(strip_tags((string) $t->summary), 110) }}</p>@endif
                    <div class="mt-5 flex flex-wrap gap-3">
                        <x-button variant="primary" size="sm" @click="$store.modal.open({ tourType: {{ \Illuminate\Support\Js::from($t->name) }} })">Get a Quote</x-button>
                        <x-button :href="route('tours.show', $t->slug)" variant="light" size="sm" icon="arrow-right">Explore</x-button>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- 5. PACKAGES: three trips per tour type --}}
<section class="bg-ivory py-16 sm:py-24" aria-labelledby="ht-packages">
    <div class="container-page space-y-16">
        <div class="mx-auto max-w-2xl text-center">
            @include('partials.ht-eyebrow', ['label' => 'Signature journeys', 'center' => true])
            <h2 id="ht-packages" class="mt-5 font-serif text-heading">@heading('Trips for *Every* Traveller')</h2>
        </div>
        @foreach($tourTypes as $t)
            @php $list = $t->packages->take(3); @endphp
            @if($list->isNotEmpty())
                <div>
                    <div class="flex flex-wrap items-end justify-between gap-4 border-b border-ink/15 pb-4">
                        <h3 class="font-display text-3xl">{{ $t->name }}</h3>
                        <a href="{{ route('tours.show', $t->slug) }}" class="inline-flex min-h-11 items-center gap-2 rounded-full border-2 border-ink px-5 text-sm font-semibold hover:bg-ink hover:text-cream">View all @icon('arrow-right', 'size-4')</a>
                    </div>
                    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($list as $package)@include('partials.package-card', ['package' => $package])@endforeach
                    </div>
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
