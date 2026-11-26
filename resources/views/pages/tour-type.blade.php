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
    <div class="container-wide"> {{-- boxed at 1390px --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <x-section-heading id="trips-title" :title="$type->name.' trips'" sub="Sample trips to start from. Every one can be changed to fit your dates, pace and budget." />
            <x-button square :href="route('packages.index', ['type' => $type->slug])" variant="outline" icon="arrow-right" class="hover:!border-cta-hover hover:!bg-cta-hover hover:!text-white">Filter all packages</x-button>
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

{{-- FINAL CTA: the shared closing band --}}
@include('partials.closing-band', ['label' => $type->name, 'heading' => 'Ready for *'.strtolower($type->name).' in Belgium?*', 'modalType' => $type->name])
@endsection
