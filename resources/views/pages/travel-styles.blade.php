@extends('layouts.app')

@section('title', 'Travel Styles: ways to see Belgium | '.$site->brand)
@section('description', 'Medieval cities, art and architecture, food and chocolate, beer and abbeys, history and the countryside: pick a travel style and see the Belgium trips built around it.')
@section('hero', '1')
@section('footer_flush', '1')

@push('schema')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Travel styles', 'item' => route('travel-styles.index')],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
@include('partials.quiz-hero', [
    'heroTitle' => 'Travel styles for Belgium',
    'heroText' => 'Pick the kind of trip you want and we will show you the journeys built around it.',
    'heroImage' => 'images/photos/bruges-canal',
    'heroAlt' => 'A canal lined with brick houses and the Belfry tower in Bruges',
    'crumb' => 'Travel styles',
])

<section class="bg-white py-16 sm:py-24" aria-labelledby="all-styles-title">
    <div class="container-wide">
        <x-section-heading id="all-styles-title" title="All our *travel styles*" sub="Pick a style to see the trips that fit it." />

        @if($styles->isEmpty())
            <p class="mt-10 bg-paper p-6 shadow-card">Travel styles are coming soon. <a href="{{ route('packages.index') }}" class="font-semibold underline">Browse all packages</a> in the meantime.</p>
        @else
            <div class="mt-10">
                @include('partials.place-tiles', ['label' => 'Travel styles', 'items' => $styles->map(fn ($s) => ['name' => $s->name, 'url' => route('travel-styles.show', $s->slug), 'image' => $s->image, 'focus' => $s->image_focus, 'sub' => $s->packages_count === 1 ? '1 tour' : $s->packages_count.' tours'])->all()])
            </div>
        @endif
    </div>
</section>

@include('partials.closing-band', ['label' => 'Your Belgium trip', 'heading' => 'Not sure which *style* suits you?', 'modalType' => ''])
@endsection
