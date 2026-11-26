@extends('layouts.app')

@section('title', $destination->seo_title ?: $destination->name.' Tours and Trips | '.$site->brand)
@section('description', $destination->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) ($destination->intro ?: $destination->description)), 155))
@section('hero', '1')
@if($destination->image)@section('og_image', asset($destination->image.'-1600.webp'))@endif

@push('schema')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Destinations', 'item' => route('destinations.index')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $destination->name, 'item' => route('destinations.show', $destination->slug)],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('footer_flush', '1')

@section('content')
@include('partials.quiz-hero', [
    'heroTitle' => 'Explore '.$destination->name,
    'heroText' => $destination->description,
    'heroImage' => $destination->image,
    'heroAlt' => $destination->image_alt ?: $destination->name,
    'crumbMid' => [route('destinations.index'), 'Destinations'],
    'crumb' => $destination->name,
])

{{-- TRIPS THAT VISIT IT --}}
<section class="bg-ivory py-16 sm:py-24" aria-labelledby="dest-trips-title">
    <div class="container-wide">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <x-section-heading id="dest-trips-title" :title="'Trips that visit '.$destination->name" sub="Sample trips to start from. Every one can be changed to fit your dates, pace and budget." />
            <x-button square :href="route('packages.index', ['experience' => $destination->slug])" variant="outline" icon="arrow-right" class="hover:!border-cta-hover hover:!bg-cta-hover hover:!text-white">Filter all packages</x-button>
        </div>
        @if($packages->isEmpty())
            <p class="mt-10 bg-paper p-6 shadow-card">We are adding trips that visit {{ $destination->name }}. In the meantime <button type="button" class="font-semibold underline" @click="$store.modal.open()">tell us what you have in mind</button> and we will plan one for you.</p>
        @else
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($packages as $package)@include('partials.package-card', ['package' => $package, 'level' => 3])@endforeach
            </div>
        @endif
    </div>
</section>

{{-- OTHER DESTINATIONS --}}
@if($others->isNotEmpty())
<section class="bg-white py-16 sm:py-24" aria-labelledby="dest-more-title">
    <div class="container-wide">
        <h2 id="dest-more-title" class="text-heading">@heading('More destinations to *explore*')</h2>
        <div class="mt-10">
            @include('partials.place-tiles', ['label' => 'Other destinations', 'items' => $others->map(fn ($o) => ['name' => $o->name, 'url' => route('destinations.show', $o->slug), 'image' => $o->image, 'focus' => $o->image_focus, 'sub' => $o->packages_count === 1 ? '1 tour' : $o->packages_count.' tours'])->all()])
        </div>
    </div>
</section>
@endif

@include('partials.closing-band', ['label' => $destination->name, 'heading' => 'Ready for *'.$destination->name.'?*', 'modalType' => ''])
@endsection