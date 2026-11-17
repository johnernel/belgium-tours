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
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Testimonials', 'item' => route('testimonials')],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
@include('partials.quiz-hero', [
    'heroTitle' => $page->block('hero_headline', 'What Travellers *Say About Us*'),
    'heroText' => $page->block('hero_sub', 'Read what other travellers say about their Belgium trips.'),
    'heroImage' => $page->block('hero_image', 'images/photos/ghent-graslei'),
    'heroAlt' => $page->block('hero_image_alt', ''),
    'crumb' => 'Testimonials',
])

<section class="bg-ivory py-16 sm:py-24" aria-labelledby="tp-title">
    <div class="container-page">
        <p class="flex items-center gap-4 text-xs font-semibold uppercase tracking-[0.22em] text-slate">
            <span class="h-px w-10 bg-amber" aria-hidden="true"></span>Kind words
        </p>
        <h2 id="tp-title" class="mt-5 max-w-2xl text-heading">@heading('Stories From Our Travellers')</h2>
        @if($page->body)<div class="prose-page mt-6 max-w-2xl">{!! $page->body !!}</div>@endif

        @if($testimonials->contains(fn ($t) => $t->is_placeholder))
            <p class="mt-6 max-w-2xl rounded-2xl bg-amber/20 px-5 py-3 text-sm text-ink">Some reviews below are placeholders: replace them with real, permitted customer reviews before launch.</p>
        @endif

        @if($testimonials->isEmpty())
            <p class="mt-10 text-lg text-slate">Traveller reviews are coming soon.</p>
        @else
            <ul class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach($testimonials as $t)
                    <li>
                        <figure class="flex h-full flex-col rounded-3xl bg-paper p-7 shadow-card">
                            <span class="flex size-11 items-center justify-center rounded-full bg-amber/25 font-serif text-4xl leading-none text-amber-ink" aria-hidden="true"><span class="translate-y-1.5">&ldquo;</span></span>
                            <blockquote class="mt-5 flex-1 font-serif text-xl font-light leading-snug text-ink">{{ $t->quote }}</blockquote>
                            <figcaption class="mt-6 flex items-center gap-3 border-t border-ink/10 pt-5">
                                <span class="relative size-12 shrink-0 overflow-hidden rounded-full">
                                    @if($t->photo)
                                        <x-photo :base="$t->photo" :alt="$t->photo_alt ?: ''" :widths="[480, 800]" sizes="48px" :ratio="[480, 480]" class="size-full" />
                                    @else
                                        <span class="flex size-full items-end justify-center {{ ['bg-sky', 'bg-cream-deep', 'bg-slate/40'][$loop->index % 3] }}" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" class="size-[85%] text-ink/70" fill="currentColor"><circle cx="12" cy="8.5" r="4" /><path d="M3.5 24c0-5 3.8-8 8.5-8s8.5 3 8.5 8z" /></svg>
                                        </span>
                                    @endif
                                </span>
                                <span>
                                    <span class="block text-sm font-semibold text-ink">{{ $t->name }}</span>
                                    @if($t->location)<span class="block text-xs font-semibold uppercase tracking-[0.16em] text-slate">{{ $t->location }}</span>@endif
                                </span>
                            </figcaption>
                        </figure>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
@endsection
