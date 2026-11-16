@extends('layouts.app')

@section('title', $type->seo_title ?: $type->name.' tours of Belgium | '.$site->brand)
@section('description', $type->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) ($type->intro ?: $type->summary)), 155))
@section('hero', '1')
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
<section class="relative isolate -mt-[4.5rem] flex min-h-[40rem] items-center overflow-hidden bg-ink pt-[4.5rem] text-white lg:min-h-[44rem]">
    @if($type->image)
        <x-photo :base="$type->image" :alt="$type->image_alt ?: $type->name" sizes="100vw" :eager="true" class="absolute inset-0 z-0 size-full" />
    @endif
    <div class="absolute inset-0 z-[1] bg-gradient-to-b from-ink/55 via-ink/35 to-ink/85"></div>
    <div class="absolute inset-0 z-[1] bg-gradient-to-r from-ink/75 via-ink/35 to-transparent"></div>

    <div class="container-page relative z-10 grid w-full items-center gap-10 py-14 sm:py-16 lg:grid-cols-[1.1fr_0.9fr] lg:gap-14 lg:py-20">
        <div>
            <p class="hero-rise mb-4 text-sm font-semibold text-white/85"><a href="{{ route('home') }}" class="underline decoration-amber decoration-2 underline-offset-4">Home</a> / {{ $type->name }}</p>
            <h1 class="hero-rise hero-rise-2 max-w-[16ch] font-serif text-5xl font-medium leading-[1.02] !text-white sm:text-6xl lg:max-w-[14ch] lg:text-[4.5rem]">{{ $type->headline ?: $type->name }}</h1>
            <p class="hero-rise hero-rise-2 mt-8 max-w-lg text-lg text-white/90 sm:text-xl">{{ $type->intro ?: $type->summary }}</p>
        </div>

        <div class="hero-rise hero-rise-2 lg:w-full lg:max-w-[28rem] lg:justify-self-end">
            @include('partials.quiz')
        </div>
    </div>
</section>

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
<section class="bg-cream-deep/60 py-14 sm:py-16" aria-labelledby="others-title">
    <div class="container-page">
        <h2 id="others-title" class="text-2xl sm:text-3xl">Other ways to see Belgium</h2>
        <ul class="mt-6 grid gap-4 sm:grid-cols-2">
            @foreach($others as $o)
                <li class="rounded-2xl bg-paper p-5 shadow-card">
                    <h3 class="text-xl"><a href="{{ route('tours.show', $o->slug) }}" class="underline decoration-amber decoration-2 underline-offset-4">{{ $o->name }}</a></h3>
                    <p class="mt-2 text-slate">{{ $o->summary }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif

{{-- FINAL CTA --}}
<section class="pt-16 sm:pt-20" aria-labelledby="cta-title">
    <div class="container-page">
        <div class="rounded-[2rem] bg-slate px-6 py-12 text-center text-white sm:px-12">
            <h2 id="cta-title" class="text-3xl !text-white sm:text-4xl">Ready for {{ strtolower($type->name) }} in Belgium?</h2>
            <p class="mx-auto mt-3 max-w-xl text-lg text-white/90">Tell us when you want to go and who is coming. We will send a free quote.</p>
            <div class="mt-7 flex flex-wrap justify-center gap-3">
                <x-button size="lg" @click="$store.modal.open({ tourType: {{ \Illuminate\Support\Js::from($type->name) }} })">Get a free quote</x-button>
                <x-button size="lg" variant="light" @click="$store.modal.open({ mode: 'question', tourType: {{ \Illuminate\Support\Js::from($type->name) }} })">Ask us a question</x-button>
            </div>
        </div>
    </div>
</section>
@endsection
