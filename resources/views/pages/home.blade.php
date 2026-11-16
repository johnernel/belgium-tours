@extends('layouts.app')

@section('title', $page->seo_title ?: $page->title)
@section('description', $page->meta_description)
@if($page->noindex)@section('noindex', '1')@endif

@push('preload')
    <link rel="preload" href="/fonts/playfair-display-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/playfair-display-latin-wght-italic.woff2" as="font" type="font/woff2" crossorigin>
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
    <x-photo base="images/photos/bruges-canal" alt="A canal lined with brick houses and the Belfry tower in Bruges, Belgium" sizes="100vw" :eager="true" class="absolute inset-0 z-0 size-full" />
    <div class="absolute inset-0 z-[1] bg-gradient-to-b from-ink/55 via-ink/35 to-ink/85"></div>
    <div class="absolute inset-0 z-[1] bg-gradient-to-r from-ink/70 via-ink/25 to-transparent"></div>

    <div class="container-page relative z-10 flex flex-1 flex-col justify-end pb-6 pt-32 sm:pb-8">
        <h1 class="hero-rise max-w-[18ch] font-serif text-5xl font-medium leading-[1] !text-white sm:text-6xl lg:max-w-none lg:text-[5.75rem]">
            @if($line2)<span class="block">{{ $line1 }}</span>@endif
            <span class="block">{{ $tailPlain }}@if($tailPlain) @endif<em class="text-amber">{{ $tailEm }}</em></span>
        </h1>
        <p class="hero-rise hero-rise-2 mt-8 max-w-xl text-lg text-white/90 sm:text-xl lg:mt-10">{{ $c('hero_sub') }}</p>

        {{-- Way-to-travel picker + trust marquee. A plain GET form: works without JavaScript. --}}
        <form method="GET" action="{{ route('packages.index') }}" aria-label="Find trips by way of travelling"
              class="hero-rise hero-rise-3 mt-8 flex flex-col gap-3 rounded-[2rem] bg-ink/45 p-3 ring-1 ring-white/20 backdrop-blur-md lg:mt-10 lg:flex-row lg:items-center lg:rounded-full lg:p-2.5 lg:pl-6">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 lg:flex-nowrap">
                <label for="hero-way" class="text-sm font-semibold text-white/85 lg:whitespace-nowrap">Way to see your destinations</label>
                <div class="relative">
                    {{-- Choosing an option goes straight to that tour type's packages (the arrow button covers no-JS). --}}
                    <select id="hero-way" name="type" @change="if ($el.value) $el.form.submit()"
                            class="min-h-10 w-full cursor-pointer appearance-none rounded-full bg-white py-1.5 pl-4 pr-9 text-sm font-semibold text-ink hover:bg-cream">
                        <option value="">Tour Types</option>
                        @foreach($tourTypes as $t)<option value="{{ $t->slug }}">{{ $t->name }}</option>@endforeach
                    </select>
                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-ink">@icon('chevron-down', 'size-4')</span>
                </div>
            </div>

            <div class="marquee min-w-0 flex-1 overflow-hidden" role="region" aria-label="Why travellers trust us">
                <div class="marquee-track">
                    @foreach([false, true] as $copy)
                        <ul class="flex shrink-0 items-center gap-x-8 pr-8" @if($copy) aria-hidden="true" @endif>
                            @foreach($trust as [$ic, $text])
                                <li class="inline-flex items-center gap-2 whitespace-nowrap text-sm font-semibold text-white/90">@icon($ic, 'size-4 shrink-0 text-amber') {{ $text }}</li>
                            @endforeach
                        </ul>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="inline-flex min-h-12 shrink-0 items-center justify-center gap-2 rounded-full bg-amber px-6 font-display font-semibold text-ink hover:bg-amber-deep lg:size-12 lg:px-0">
                <span class="lg:sr-only">Show trips</span>@icon('arrow-right', 'size-5')
            </button>
        </form>
    </div>
</section>

{{-- 4. WHY TRAVELLERS CHOOSE US --}}
<section class="relative py-16 sm:py-24" aria-labelledby="why-title">
    <div class="container-page grid items-center gap-12 lg:grid-cols-[0.9fr_1.1fr]">
        <div class="grid grid-cols-3 items-end gap-3 sm:gap-4" aria-hidden="false">
            <x-photo base="images/photos/flower-carpet" alt="Flower carpet laid out in front of the Brussels Grand Place" sizes="(min-width:1024px) 14vw, 30vw" :ratio="[1600, 1023]" class="aspect-[3/4] w-full rounded-3xl shadow-card" />
            <x-photo base="images/photos/gravensteen" alt="The medieval Gravensteen castle in Ghent at sunset" sizes="(min-width:1024px) 14vw, 30vw" :ratio="[1600, 1044]" class="aspect-[3/5] w-full rounded-3xl shadow-card" />
            <x-photo base="images/photos/la-roche" alt="The Ourthe river and wooded hills at La Roche-en-Ardenne" sizes="(min-width:1024px) 14vw, 30vw" class="aspect-[3/4] w-full rounded-3xl shadow-card" />
        </div>
        <div>
            <x-section-heading id="why-title" :title="$c('why_heading')" :sub="$c('why_sub')" />
            <ul class="mt-8 grid gap-x-8 gap-y-6 sm:grid-cols-2">
                @foreach([['compass', 1], ['map-pin', 2], ['wallet', 3], ['phone', 4]] as [$ic, $n])
                    <li class="flex gap-4">
                        <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-sky text-ink">@icon($ic)</span>
                        <div>
                            <h3 class="font-display text-lg font-semibold">{{ $c("why_{$n}_title") }}</h3>
                            <p class="mt-1 text-slate">{{ $c("why_{$n}_text") }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
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
                        <a href="{{ route('packages.index', ['type' => $t->slug]) }}" class="mt-4 inline-flex min-h-8 items-center gap-1.5 font-semibold underline decoration-amber decoration-2 underline-offset-4">
                            See {{ $t->name }} trips @icon('arrow-right', 'size-4')
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
