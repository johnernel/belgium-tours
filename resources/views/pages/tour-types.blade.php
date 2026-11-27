@extends('layouts.app')

@section('title', 'Tour Types: ways to travel Belgium | '.$site->brand)
@section('description', 'Self drive, chauffeur driven and coach tours of Belgium: pick the way you want to travel and see the trips built for it.')
@section('hero', '1')
@section('footer_flush', '1')

@push('schema')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Tour Types', 'item' => route('tours.index')],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
@include('partials.quiz-hero', [
    'heroTitle' => 'Ways to travel Belgium',
    'heroText' => 'Pick the way you want to travel and we will show you the trips built for it.',
    'heroImage' => 'images/photos/gravensteen',
    'heroAlt' => 'The Gravensteen castle in Ghent',
    'crumb' => 'Tour Types',
])

{{-- Same tile grid as the Destinations and Travel Styles pages (see pages/destinations.blade.php) --}}
<section class="bg-white py-[90px]" aria-labelledby="all-types-title">
    <div class="container-wide">
        <h2 id="all-types-title" class="sr-only">All Our Tour Types</h2>

        @if($types->isEmpty())
            <p class="bg-paper p-6 shadow-card">Tour types are coming soon. <a href="{{ route('packages.index') }}" class="font-semibold underline">Browse all packages</a> in the meantime.</p>
        @else
            <ul class="grid gap-[2px] bg-white sm:grid-cols-2 lg:grid-cols-3">
                @foreach($types as $t)
                    <li class="flex">
                        <article class="group relative isolate flex aspect-[16/15] w-full flex-col justify-end overflow-hidden bg-ink text-white">
                            @if($t->image)
                                <x-photo :base="$t->image" :alt="$t->image_alt ?: $t->name" sizes="(min-width:1024px) 33vw, (min-width:640px) 50vw, 100vw"
                                         class="absolute inset-0 -z-20 size-full transition-transform duration-700 group-hover:scale-105" />
                            @endif
                            <span class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/80 via-ink/35 to-ink/20 transition-colors duration-500 group-hover:from-ink/90" aria-hidden="true"></span>
                            <div class="p-5 sm:p-6">
                                <p class="text-[0.65rem] font-semibold uppercase tracking-[0.2em] text-amber-light">{{ $t->packages_count === 1 ? '1 tour' : $t->packages_count.' tours' }}</p>
                                <h3 class="mt-1.5 text-3xl text-white"><a href="{{ route('tours.show', $t->slug) }}" class="after:absolute after:inset-0">{{ $t->name }}</a></h3>
                                <div class="mt-3 border-t border-white/30 pt-3">
                                    @if($t->summary ?: $t->menu_text)<p class="line-clamp-2 text-xs leading-relaxed text-white/85">{{ $t->summary ?: $t->menu_text }}</p>@endif
                                    <p class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-white">Discover {{ $t->name }} @icon('arrow-right', 'size-3.5 transition-transform group-hover:translate-x-1')</p>
                                </div>
                            </div>
                        </article>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>

@include('partials.closing-band', ['label' => 'Your Belgium trip', 'heading' => 'Not sure which *way to travel* suits you?', 'modalType' => ''])
@endsection
