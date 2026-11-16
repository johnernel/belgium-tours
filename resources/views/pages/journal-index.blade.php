@extends('layouts.app')

@section('title', 'The Journal: Stories from Belgium | '.$site->brand)
@section('description', 'Stories and travel notes from Belgium: Bruges, Ghent, Brussels, the Ardennes and the slow way to see them.')
@if(! $indexable)@section('noindex', '1')@endif

@push('schema')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Journal', 'item' => route('journal.index')],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
<section class="py-12 sm:py-16">
    <div class="container-page">
        <nav aria-label="Breadcrumb" class="mb-4 text-sm text-slate"><a href="{{ route('home') }}" class="underline">Home</a> / Journal</nav>
        <p class="flex items-center gap-4 text-xs font-semibold uppercase tracking-[0.22em] text-slate">
            <span class="h-px w-10 bg-amber" aria-hidden="true"></span>The Journal
        </p>
        <h1 class="mt-5 max-w-2xl text-heading heading-amber">@heading('Stories from the *Lowlands.*')</h1>

        @if($posts->isEmpty())
            <p class="mt-10 text-lg text-slate">No articles yet. Please check back soon.</p>
        @else
            <div class="mt-12 grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($posts as $post)
                    @include('partials.post-card', ['post' => $post])
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
