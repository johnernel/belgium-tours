@extends('layouts.app')

@section('title', 'Belgium Tour Packages | '.$site->brand)
@section('description', 'Browse self drive, chauffeur driven and coach tour packages in Belgium. Every trip can be tailored. Request a free quote.')
@if($activeType || $activeDestination)@section('canonical', route('packages.index'))@endif

@push('schema')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Packages', 'item' => route('packages.index')],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
<section class="py-12 sm:py-16">
    <div class="container-page">
        <nav aria-label="Breadcrumb" class="mb-4 text-sm text-slate"><a href="{{ route('home') }}" class="underline">Home</a> / Packages</nav>
        <x-section-heading tag="h1" title="Belgium tour packages" sub="Sample trips to start from. Every one can be changed to suit your dates, pace and budget." />

        <form method="GET" action="{{ route('packages.index') }}" class="mt-8 flex flex-wrap items-end gap-3" aria-label="Filter packages">
            <div>
                <label for="flt-type" class="mb-1 block text-sm font-semibold">Tour type</label>
                <select id="flt-type" name="type" class="min-h-11 rounded-xl border-2 border-slate/30 bg-white px-3 focus:border-ink focus:outline-none">
                    <option value="">All tour types</option>
                    @foreach($tourTypes as $t)<option value="{{ $t->slug }}" @selected($activeType === $t->slug)>{{ $t->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="flt-dest" class="mb-1 block text-sm font-semibold">Destination</label>
                <select id="flt-dest" name="destination" class="min-h-11 rounded-xl border-2 border-slate/30 bg-white px-3 focus:border-ink focus:outline-none">
                    <option value="">All destinations</option>
                    @foreach($destinations as $d)<option value="{{ $d->slug }}" @selected($activeDestination === $d->slug)>{{ $d->name }}</option>@endforeach
                </select>
            </div>
            <x-button type="submit" variant="dark">Filter</x-button>
            @if($activeType || $activeDestination)<a href="{{ route('packages.index') }}" class="min-h-11 py-3 font-semibold underline">Clear</a>@endif
        </form>

        @if($packages->isEmpty())
            <p class="mt-10 rounded-2xl bg-paper p-6 shadow-card">No packages match those filters yet. <a href="{{ route('packages.index') }}" class="font-semibold underline">Show all packages</a> or <button type="button" class="font-semibold underline" @click="$store.modal.open()">ask us to plan one for you</button>.</p>
        @else
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($packages as $package)@include('partials.package-card', ['package' => $package])@endforeach
            </div>
        @endif
    </div>
</section>
@endsection
