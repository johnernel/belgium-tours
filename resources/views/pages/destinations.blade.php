@extends('layouts.app')

@section('title', $page->seo_title ?: 'Destinations | '.$site->brand)
@section('description', (string) ($page->meta_description ?? ''))
@section('hero', '1')
@section('footer_flush', '1')
@if($page->noindex)@section('noindex', '1')@endif

@push('schema')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Destinations', 'item' => route('destinations.index')],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
@include('partials.quiz-hero', [
    'heroTitle' => $page->block('hero_headline', 'Destinations worth *slowing down* for'),
    'heroText' => $page->block('hero_sub', 'Five things to build a Belgium trip around. Pick one and we will show you the tours that go there.'),
    'heroImage' => $page->block('hero_image', 'images/photos/bruges-market'),
    'heroAlt' => $page->block('hero_image_alt', 'The Belfry on the Market Square in Bruges at dusk'),
    'crumb' => 'Destinations',
    'heroTwoLines' => true,
])

{{-- Layout copied from the client's reference: a gapless grid of full-bleed photo tiles (3 across on desktop), each with a dark wash and the text at the bottom left:
     small uppercase line, name, hairline, short line, "Discover" link. The whole tile is one link. --}}
<section class="bg-white py-[90px]" aria-labelledby="all-dest-title">
    <div class="container-wide">
        <h2 id="all-dest-title" class="sr-only">All our destinations</h2>

        @if($experiences->isEmpty())
            <p class="bg-paper p-6 shadow-card">Destinations are coming soon. <a href="{{ route('packages.index') }}" class="font-semibold underline">Browse all packages</a> in the meantime.</p>
        @else
            <ul class="grid gap-[2px] bg-white sm:grid-cols-2 lg:grid-cols-3">
                @foreach($experiences as $e)
                    <li class="flex">
                        <article class="group relative isolate flex aspect-[16/15] w-full flex-col justify-end overflow-hidden bg-ink text-white">
                            @if($e->image)
                                <x-photo :base="$e->image" :alt="$e->image_alt ?: $e->name" sizes="(min-width:1024px) 33vw, (min-width:640px) 50vw, 100vw"
                                         class="absolute inset-0 -z-20 size-full transition-transform duration-700 group-hover:scale-105" style="object-position: {{ $e->image_focus ?: '50% 50%' }}" />
                            @endif
                            <span class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/80 via-ink/35 to-ink/20 transition-colors duration-500 group-hover:from-ink/90" aria-hidden="true"></span>
                            <div class="p-5 sm:p-6">
                                <p class="text-[0.65rem] font-semibold uppercase tracking-[0.2em] text-amber-light">{{ $e->packages_count === 1 ? '1 tour' : $e->packages_count.' tours' }}</p>
                                <h3 class="mt-1.5 text-3xl text-white"><a href="{{ route('destinations.show', $e->slug) }}" class="after:absolute after:inset-0">{{ $e->name }}</a></h3>
                                <div class="mt-3 border-t border-white/30 pt-3">
                                    @if($e->description)<p class="line-clamp-2 text-xs leading-relaxed text-white/85">{{ $e->description }}</p>@endif
                                    <p class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-white">Discover {{ $e->name }} @icon('arrow-right', 'size-3.5 transition-transform group-hover:translate-x-1')</p>
                                </div>
                            </div>
                        </article>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>

@include('partials.closing-band', ['label' => 'Your Belgium trip', 'heading' => 'Not sure which *destination* suits you?', 'modalType' => ''])
@endsection
