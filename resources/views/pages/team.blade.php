@extends('layouts.app')

@section('title', $page->seo_title ?: $page->title.' | '.$site->brand)
@section('description', (string) ($page->meta_description ?? ''))
@section('hero', '1')
@if($noindex)@section('noindex', '1')@endif

@push('schema')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Meet Our Team', 'item' => route('team')],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
@include('partials.quiz-hero', [
    'heroTitle' => $page->block('hero_headline', 'Meet Our *Team*'),
    'heroText' => $page->block('hero_sub', 'The people who plan your Belgium trip and look after you along the way.'),
    'heroImage' => $page->block('hero_image', 'images/photos/countryside'),
    'heroAlt' => $page->block('hero_image_alt', ''),
    'crumb' => 'Meet Our Team',
])

<section class="py-16 sm:py-24" aria-labelledby="team-title">
    <div class="container-page">
        <p class="flex items-center gap-4 text-xs font-semibold uppercase tracking-[0.22em] text-slate">
            <span class="h-px w-10 bg-amber" aria-hidden="true"></span>Our team
        </p>
        <h2 id="team-title" class="mt-5 max-w-2xl text-heading">@heading('The People Behind Your Trip')</h2>
        @if($page->body)<div class="prose-page mt-6 max-w-2xl">{!! $page->body !!}</div>@endif

        @if($members->contains(fn ($m) => $m->is_placeholder))
            <p class="mt-6 max-w-2xl rounded-2xl bg-amber/20 px-5 py-3 text-sm text-ink">Sample team: placeholder entries to be replaced with the real team before launch.</p>
        @endif

        @if($members->isEmpty())
            <p class="mt-10 text-lg text-slate">Our team introductions are coming soon.</p>
        @else
            <ul class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($members as $m)
                    <li class="flex flex-col overflow-hidden rounded-3xl bg-paper shadow-card">
                        <div class="relative aspect-[4/3] overflow-hidden bg-sky-soft">
                            @if($m->photo)
                                <x-photo :base="$m->photo" :alt="$m->photo_alt ?: $m->name" sizes="(min-width:1024px) 30vw, (min-width:640px) 45vw, 92vw" fetchpriority="low" :ratio="[1200, 900]" class="absolute inset-0 size-full" />
                            @else
                                {{-- Neutral avatar until a real, permitted photo is uploaded --}}
                                <span class="flex size-full items-end justify-center {{ ['bg-sky', 'bg-cream-deep', 'bg-slate/40'][$loop->index % 3] }}" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" class="h-[80%] text-ink/60" fill="currentColor"><circle cx="12" cy="8.5" r="4" /><path d="M3.5 24c0-5 3.8-8 8.5-8s8.5 3 8.5 8z" /></svg>
                                </span>
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <h3 class="text-2xl">{{ $m->name }}</h3>
                            @if($m->role)<p class="mt-1 text-xs font-semibold uppercase tracking-[0.16em] text-amber-ink">{{ $m->role }}</p>@endif
                            @if($m->bio)<p class="mt-3 text-[0.95rem] leading-relaxed text-slate">{{ $m->bio }}</p>@endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
@endsection
