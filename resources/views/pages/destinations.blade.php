@extends('layouts.app')

@section('title', $page->seo_title ?: 'Destinations | '.$site->brand)
@section('description', (string) ($page->meta_description ?? ''))
@section('hero', '1')
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
])

<section class="py-16 sm:py-24" aria-labelledby="all-dest-title">
    <div class="container-page">
        <x-section-heading id="all-dest-title" title="All our *destinations*" sub="Pick a place to see the tours that go there." />

        @if($experiences->isEmpty())
            <p class="mt-10 rounded-2xl bg-paper p-6 shadow-card">Destinations are coming soon. <a href="{{ route('packages.index') }}" class="font-semibold underline">Browse all packages</a> in the meantime.</p>
        @else
            <ul class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($experiences as $e)
                    <li class="flex">
                        <article class="group flex w-full flex-col overflow-hidden rounded-[1.5rem] bg-paper shadow-card">
                            <div class="aspect-[4/3] overflow-hidden bg-sky-soft">
                                @if($e->image)
                                    <x-photo :base="$e->image" :alt="$e->image_alt ?: $e->name" sizes="(min-width:1024px) 30vw, (min-width:640px) 46vw, 92vw"
                                             class="size-full transition-transform duration-700 group-hover:scale-105" style="object-position: {{ $e->image_focus ?: '50% 50%' }}" />
                                @endif
                            </div>
                            <div class="flex flex-1 flex-col p-6">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate">{{ $e->packages_count === 1 ? '1 tour' : $e->packages_count.' tours' }}</p>
                                <h3 class="mt-2 text-3xl"><a href="{{ route('destinations.show', $e->slug) }}" class="hover:text-amber-ink">{{ $e->name }}</a></h3>
                                <a href="{{ route('destinations.show', $e->slug) }}" class="mt-5 inline-flex min-h-11 items-center gap-2 font-semibold underline decoration-amber decoration-2 underline-offset-4 hover:text-slate">
                                    Explore {{ $e->name }}<span class="sr-only"> and its tours</span> @icon('arrow-right', 'size-4')
                                </a>
                            </div>
                        </article>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
@endsection
