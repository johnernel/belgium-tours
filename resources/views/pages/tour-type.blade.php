@extends('layouts.app')

@section('title', $type->seo_title ?: $type->name.' tours of Belgium | '.$site->brand)
@section('description', $type->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) ($type->intro ?: $type->summary)), 155))
@section('hero', '1')
@section('footer_flush', '1') {{-- the last section is a full-width photo band, so the footer sits right under it --}}
@if($type->image)@section('og_image', asset($type->image.'-1600.webp'))@endif

@push('preload')
    @if($type->image)
        <link rel="preload" as="image" href="{{ asset($type->image.'-800.webp') }}" imagesrcset="{{ asset($type->image.'-480.webp') }} 480w, {{ asset($type->image.'-800.webp') }} 800w, {{ asset($type->image.'-1280.webp') }} 1280w" imagesizes="100vw" fetchpriority="high">
    @endif
@endpush

@push('schema')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $type->name, 'item' => route('tours.show', $type->slug)],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
{{-- HERO: headline and short description on the left, the quiz on the right --}}
@include('partials.quiz-hero', [
    'heroTitle' => $type->headline ?: $type->name,
    'heroText' => $type->intro ?: $type->summary,
    'heroImage' => $type->image,
    'heroAlt' => $type->image_alt ?: $type->name,
    'crumb' => $type->name,
])

{{-- TRIPS OF THIS TYPE --}}
<section class="py-16 sm:py-20" aria-labelledby="trips-title">
    <div class="container-page">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <x-section-heading id="trips-title" :title="$type->name.' trips'" sub="Sample trips to start from. Every one can be changed to fit your dates, pace and budget." />
            <x-button :href="route('packages.index', ['type' => $type->slug])" variant="outline" icon="arrow-right">Filter all packages</x-button>
        </div>
        @if($packages->isEmpty())
            <p class="mt-10 rounded-2xl bg-paper p-6 shadow-card">We are adding {{ strtolower($type->name) }} trips. In the meantime <button type="button" class="font-semibold underline" @click="$store.modal.open({ tourType: @js($type->name) })">tell us what you have in mind</button> and we will plan one for you.</p>
        @else
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($packages as $package)@include('partials.package-card', ['package' => $package, 'level' => 3])@endforeach
            </div>
        @endif
    </div>
</section>

{{-- OTHER WAYS TO TRAVEL --}}
@if($others->isNotEmpty())
<section class="bg-white py-16 sm:py-24" aria-labelledby="others-title">
    <div class="container-wide">
        <h2 id="others-title" class="text-heading">@heading('Other ways to see Belgium')</h2>
        {{-- Photo cards like the home "Three Ways to Travel" cards (shared partial): two across from sm up, stacked on phones --}}
        <div class="mt-10 grid gap-5 sm:grid-cols-2">
            @foreach($others as $o)
                @include('partials.tour-type-card', ['t' => $o])
            @endforeach
        </div>
    </div></section>
@endif

{{-- FINAL CTA: solid cream band (#F7F3EA), centered label with hairlines, two-colour heading, short divider, text and two buttons; a faint outlined drawing of a Belgian belfry and step-gable houses in the bottom right corner --}}
<section class="relative isolate overflow-hidden bg-cream py-24 text-center sm:py-32" aria-labelledby="cta-title">
    <svg viewBox="0 0 720 260" preserveAspectRatio="xMaxYMax meet" class="pointer-events-none absolute bottom-0 right-0 -z-10 h-auto w-[min(40rem,100%)] text-teal-deep opacity-[0.16] [mask-image:linear-gradient(to_right,transparent,#000_38%)] max-sm:opacity-[0.1]" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linejoin="round" stroke-linecap="round" aria-hidden="true">
        {{-- a thin arc behind the skyline --}}
        <path d="M718 6C640 28 566 86 522 154" stroke-width="0.9"/>
        {{-- belfry: tower, spire and windows --}}
        <path d="M52 260V110L74 8l22 102v150M52 110h44M63 62h22M58 92h32M74 8v102"/>
        <path d="M62 196v-30a12 12 0 0 1 24 0v30M62 148v-22a12 12 0 0 1 24 0v22"/>
        {{-- two step-gable houses --}}
        <path d="M112 260V172h12v-20h14v-20h14v-20h14v20h14v20h14v20h12v88"/>
        <path d="M216 260V152h12v-18h14v-18h14v-18h14v18h14v18h14v18h12v108"/>
        @for($i = 0; $i < 3; $i++)<path d="M{{ 130 + $i * 28 }} 214h10v28h-10zM{{ 234 + $i * 28 }} 196h10v30h-10z"/>@endfor
        {{-- long town hall: roof, dormers, windows and arches --}}
        <path d="M334 260V152l20-30h262l20 30v108M334 152h302"/>
        @for($i = 0; $i < 5; $i++)<path d="M{{ 372 + $i * 54 }} 122l14-28 14 28M{{ 376 + $i * 54 }} 122v-4h20v4"/>@endfor
        @for($i = 0; $i < 9; $i++)<path d="M{{ 352 + $i * 30 }} 168h14v24h-14zM{{ 352 + $i * 30 }} 214v-14a7 7 0 0 1 14 0v14"/>@endfor
        {{-- a smaller tower on the right --}}
        <path d="M656 260V142l18-74 18 74v118M656 142h36"/>
    </svg>
    <div class="container-page">
        <p class="flex items-center justify-center gap-4 text-xs font-semibold uppercase tracking-[0.22em] text-amber-ink">
            <span class="h-px w-10 bg-amber" aria-hidden="true"></span>{{ $type->name }}<span class="h-px w-10 bg-amber" aria-hidden="true"></span>
        </p>
        <h2 id="cta-title" class="mx-auto mt-6 max-w-3xl text-[2.25rem] leading-[1.1] sm:text-heading">@heading('Ready for *'.strtolower($type->name).' in Belgium?*')</h2>
        <span class="mx-auto mt-6 block h-0.5 w-12 bg-amber" aria-hidden="true"></span>
        <p class="mx-auto mt-6 max-w-xl text-lg text-ink/85">Tell us when you want to go and who is coming. We will send a free quote.</p>
        <div class="mt-9 flex flex-wrap justify-center gap-4">
            <x-button square size="lg" variant="cta" icon="arrow-right" @click="$store.modal.open({ tourType: {{ \Illuminate\Support\Js::from($type->name) }} })">Get a free quote</x-button>
            <x-button square size="lg" variant="outline" @click="$store.modal.open({ mode: 'question', tourType: {{ \Illuminate\Support\Js::from($type->name) }} })">Ask us a question</x-button>
        </div>
    </div>
</section>@endsection
