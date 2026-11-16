@extends('layouts.app')

@section('title', $post->title.' | The Journal | '.$site->brand)
@section('description', (string) ($post->excerpt ?? ''))
@if($post->is_placeholder)@section('noindex', '1')@endif

@push('schema')
    <script type="application/ld+json">{!! json_encode(array_filter([
        '@context' => 'https://schema.org', '@type' => 'BlogPosting',
        'headline' => $post->title,
        'description' => $post->excerpt,
        'image' => $post->image ? asset($post->image.'-1280.webp') : null,
        'datePublished' => ($post->published_at ?? $post->created_at)->toAtomString(),
        'dateModified' => $post->updated_at->toAtomString(),
        'mainEntityOfPage' => route('journal.show', $post->slug),
        'publisher' => ['@type' => 'Organization', 'name' => $site->brand],
    ]), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Journal', 'item' => route('journal.index')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title, 'item' => route('journal.show', $post->slug)],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
<article class="py-12 sm:py-16">
    <div class="container-page max-w-4xl">
        <nav aria-label="Breadcrumb" class="mb-4 text-sm text-slate"><a href="{{ route('home') }}" class="underline">Home</a> / <a href="{{ route('journal.index') }}" class="underline">Journal</a></nav>
        @if($post->category)<p class="text-xs font-semibold uppercase tracking-[0.22em] text-amber-ink">{{ $post->category }}</p>@endif
        <h1 class="mt-3 text-heading">{{ $post->title }}</h1>
        <p class="mt-4 text-sm text-slate">{{ ($post->published_at ?? $post->created_at)->format('j F Y') }} &middot; {{ $post->read_minutes }} min read</p>

        @if($post->is_placeholder)
            <p class="mt-6 rounded-2xl bg-amber/20 px-5 py-3 text-sm text-ink">Sample article: placeholder text to be replaced with a real story before launch.</p>
        @endif

        @if($post->image)
            <div class="relative mt-8 aspect-[16/9] overflow-hidden rounded-3xl bg-sky-soft shadow-card">
                <x-photo :base="$post->image" :alt="$post->image_alt ?: ''" sizes="(min-width:1024px) 896px, 92vw" :eager="true" :ratio="[1600, 900]" class="absolute inset-0 size-full" />
            </div>
        @endif

        <div class="prose-page mt-8 text-lg">{!! $post->body !!}</div>

        <div class="mt-12 flex flex-wrap items-center gap-4 border-t border-ink/15 pt-8">
            <x-button variant="cta" size="lg" icon="arrow-right" @click="$store.modal.open()">Plan my trip</x-button>
            <a href="{{ route('journal.index') }}" class="font-semibold underline decoration-amber decoration-2 underline-offset-4">All articles</a>
        </div>
    </div>
</article>

@if($more->isNotEmpty())
    <section class="bg-cream pb-16 sm:pb-24" aria-labelledby="more-title">
        <div class="container-page border-t border-ink/15 pt-12">
            <h2 id="more-title" class="text-heading heading-amber">@heading('More *stories*')</h2>
            <div class="mt-10 grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($more as $p)
                    @include('partials.post-card', ['post' => $p])
                @endforeach
            </div>
        </div>
    </section>
@endif
@endsection
