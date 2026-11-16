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

@section('content')
@php $c = fn ($k) => $page->block($k); @endphp

{{-- 3. HERO --}}
@php
    // Headline: break after the first comma; the last word gets the italic amber treatment (as in the design reference).
    $headline = trim($c('hero_h1') ?: 'Belgium, planned around you');
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
        <x-photo base="images/photos/brussels-grand-place" alt="" sizes="100vw" class="hero-slide hero-slide-2 absolute inset-0 size-full" />
        <x-photo base="images/photos/gravensteen" alt="" sizes="100vw" class="hero-slide hero-slide-3 absolute inset-0 size-full" />
    </div>
    <div class="absolute inset-0 z-[1] bg-gradient-to-b from-ink/55 via-ink/35 to-ink/85"></div>
    <div class="absolute inset-0 z-[1] bg-gradient-to-r from-ink/70 via-ink/25 to-transparent"></div>

    <div class="container-page relative z-10 flex flex-1 flex-col justify-end pb-6 pt-32 sm:pb-8">
        <h1 class="hero-rise max-w-[18ch] font-serif text-5xl font-medium leading-[1] !text-white sm:text-6xl lg:max-w-none lg:text-[5.75rem]">
            @if($line2)<span class="block">{{ $line1 }}</span>@endif
            <span class="block">{{ $tailPlain }}@if($tailPlain) @endif<em class="text-amber">{{ $tailEm }}</em></span>
        </h1>
        <p class="hero-rise hero-rise-2 mt-8 max-w-xl text-lg text-white/90 sm:text-xl lg:mt-10">{{ $c('hero_sub') }}</p>

        {{-- Trust indicators --}}
        <div class="marquee hero-rise hero-rise-3 mt-8 overflow-hidden rounded-full bg-ink/45 py-3.5 ring-1 ring-white/20 backdrop-blur-md lg:mt-10" role="region" aria-label="Why travellers trust us">
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

{{-- 4. WHY TRAVELLERS CHOOSE US --}}
@php
    // Last word of the heading is set in italic slate, as in the design reference.
    $why = trim($c('why_heading') ?: 'Every journey, personally crafted.');
    $whyCut = strrpos($why, ' ');
    [$whyPlain, $whyEm] = $whyCut ? [substr($why, 0, $whyCut), substr($why, $whyCut + 1)] : ['', $why];
    $whyPhotos = [
        ['images/photos/flower-carpet', 'Flower carpet laid out in front of the Brussels Grand Place', [1600, 1023]],
        ['images/photos/gravensteen', 'The medieval Gravensteen castle in Ghent at sunset', [1600, 1044]],
    ];
@endphp
<section class="relative py-16 sm:py-24" aria-labelledby="why-title">
    <div class="container-page grid gap-10 lg:grid-cols-2 lg:items-stretch lg:gap-16">
        {{-- Photos fill the full height of the text column, so both sides stay balanced --}}
        <div class="grid h-80 grid-cols-2 gap-3 sm:h-96 sm:gap-4 lg:h-auto lg:min-h-[30rem]">
            @foreach($whyPhotos as [$base, $alt, $ratio])
                <div class="relative overflow-hidden rounded-3xl shadow-card">
                    <x-photo :base="$base" :alt="$alt" sizes="(min-width:1024px) 24vw, 46vw" :ratio="$ratio" class="absolute inset-0 size-full" />
                </div>
            @endforeach
        </div>

        <div class="flex flex-col justify-center lg:max-w-xl">
            <p class="flex items-center gap-4 text-xs font-semibold uppercase tracking-[0.22em] text-slate">
                <span class="h-px w-10 bg-slate/50" aria-hidden="true"></span>{{ $c('why_eyebrow') ?: 'Tailor-made in Belgium' }}
            </p>
            <h2 id="why-title" class="mt-5 font-serif text-4xl font-normal leading-[1.08] sm:text-5xl lg:text-[3.5rem]">
                {{ $whyPlain }}@if($whyPlain) @endif<em class="text-slate">{{ $whyEm }}</em>
            </h2>
            <p class="mt-6 text-lg leading-relaxed text-slate">{{ $c('why_sub') }}</p>

            <ul class="mt-8 divide-y divide-ink/10 border-t border-ink/10">
                @foreach([1, 2, 3] as $n)
                    <li class="grid grid-cols-[2.75rem_1fr] items-baseline gap-x-2 py-4">
                        <span class="font-serif text-lg italic text-amber-deep" aria-hidden="true">{{ sprintf('%02d', $n) }}</span>
                        <div>
                            <h3 class="font-serif text-xl font-normal leading-snug">{{ $c("why_{$n}_title") }}</h3>
                            <p class="mt-1 text-[0.95rem] text-slate">{{ $c("why_{$n}_text") }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="mt-8">
                <x-button variant="cta" size="lg" icon="arrow-right" @click="$store.modal.open()">{{ $c('why_cta') ?: 'Plan My Journey' }}</x-button>
            </div>
        </div>
    </div>
</section>

{{-- 5. TOUR TYPES --}}
<section class="bg-cream-deep/60 py-16 sm:py-24" aria-labelledby="types-title">
    <div class="container-page">
        <x-section-heading id="types-title" :title="$c('tour_types_heading')" :sub="$c('tour_types_sub')" align="center" />
        <div class="mt-10 grid gap-6 md:grid-cols-3">
            @foreach($tourTypes as $t)
                <article class="flex flex-col overflow-hidden rounded-[1.5rem] bg-paper shadow-card">
                    <div class="aspect-[16/10]">
                        @if($t->image)
                            <x-photo :base="$t->image" :alt="$t->image_alt ?: $t->name" sizes="(min-width:768px) 30vw, 92vw" class="size-full" />
                        @else
                            <div class="size-full bg-sky" role="img" aria-label="Image to be added"></div>
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <h3 class="text-2xl">{{ $t->name }}</h3>
                        <p class="mt-2 flex-1 text-slate">{{ $t->summary }}</p>
                        <div class="mt-5 flex flex-wrap gap-2">
                            <x-button size="sm" @click="$store.modal.open({ tourType: '{{ $t->name }}', heading: 'Get a quote: {{ $t->name }}' })">Get a Quote</x-button>
                            <x-button size="sm" variant="outline" @click="$store.modal.open({ mode: 'question', tourType: '{{ $t->name }}' })">Ask Us a Question</x-button>
                        </div>
                        <a href="{{ route('tours.show', $t->slug) }}" class="mt-4 inline-flex min-h-8 items-center gap-1.5 font-semibold underline decoration-amber decoration-2 underline-offset-4">
                            Explore {{ $t->name }} @icon('arrow-right', 'size-4')
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- 6. FEATURED PACKAGES --}}
<section class="py-16 sm:py-24" aria-labelledby="pk-title">
    <div class="container-page">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <x-section-heading id="pk-title" :title="$c('packages_heading')" :sub="$c('packages_sub')" />
            <x-button :href="route('packages.index')" variant="outline" icon="arrow-right">All packages</x-button>
        </div>
        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($packages as $package)
                @include('partials.package-card', ['package' => $package])
            @endforeach
        </div>
    </div>
</section>

{{-- 7. HOW IT WORKS (a real sequence, so numbered) --}}
<section class="pb-16 sm:pb-24" aria-labelledby="how-title">
    <div class="container-page">
        <x-section-heading id="how-title" :title="$c('steps_heading')" align="center" />
        <ol class="mt-10 grid gap-6 md:grid-cols-3">
            @foreach([1, 2, 3] as $n)
                <li class="relative rounded-[1.5rem] bg-sky-soft p-7">
                    <span class="font-display text-5xl font-semibold text-slate/50" aria-hidden="true">{{ $n }}</span>
                    <h3 class="mt-2 text-xl">{{ $c("step_{$n}_title") }}</h3>
                    <p class="mt-2 text-slate">{{ $c("step_{$n}_text") }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- 8. TESTIMONIALS --}}
<section class="bg-amber py-16 sm:py-20" aria-labelledby="t-title">
    <div class="container-page">
        <x-section-heading id="t-title" :title="$c('testimonials_heading')" />
        <div class="mt-8 grid gap-5 md:grid-cols-3">
            @foreach($testimonials as $t)
                <figure class="rounded-[1.5rem] bg-paper p-6 shadow-card">
                    <div class="flex gap-0.5 text-amber-deep" aria-hidden="true">@for($i=0;$i<5;$i++)@icon('star', 'size-5 fill-current')@endfor</div>
                    <blockquote class="mt-3 text-ink">{{ $t->quote }}</blockquote>
                    <figcaption class="mt-4 text-sm font-semibold text-slate">{{ $t->name }}@if($t->location), {{ $t->location }}@endif</figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>

{{-- 9. FAQ --}}
<section class="py-16 sm:py-24" aria-labelledby="faq-title">
    <div class="container-page grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
        <x-section-heading id="faq-title" :title="$c('faq_heading')" />
        <div class="space-y-3">
            @foreach($faqs as $f)
                <x-faq-item :question="$f->question">{{ $f->answer }}</x-faq-item>
            @endforeach
        </div>
    </div>
</section>

{{-- 10. FINAL CTA --}}
<section class="pb-4" aria-labelledby="cta-title">
    <div class="container-page">
        <div class="relative isolate overflow-hidden rounded-[2rem] bg-slate px-6 py-14 text-center text-white sm:px-12">
            <svg class="pointer-events-none absolute -right-10 -top-6 z-0 w-72 text-white/15" viewBox="0 0 300 120" fill="none" aria-hidden="true"><path class="doodle-path" d="M5 110C70 20 150 130 220 50s60-30 75-40" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
            <h2 id="cta-title" class="relative z-10 text-3xl !text-white sm:text-4xl">{{ $c('cta_heading') }}</h2>
            <p class="relative z-10 mx-auto mt-3 max-w-xl text-lg text-white/90">{{ $c('cta_text') }}</p>
            <div class="relative z-10 mt-7 flex flex-wrap justify-center gap-3">
                <x-button size="lg" @click="$store.modal.open()">Start Your Adventure</x-button>
                @if($site->phone)<x-button size="lg" variant="light" :href="'tel:'.$site->phone_href" icon="phone">Call us</x-button>@endif
            </div>
        </div>
    </div>
</section>
@endsection
