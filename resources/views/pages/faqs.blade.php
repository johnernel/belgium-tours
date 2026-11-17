@extends('layouts.app')

@section('title', $page->seo_title ?: $page->title.' | '.$site->brand)
@section('description', (string) ($page->meta_description ?? ''))
@section('hero', '1')
@if($noindex)@section('noindex', '1')@endif

@push('schema')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org', '@type' => 'FAQPage',
        'mainEntity' => $faqs->map(fn ($f) => [
            '@type' => 'Question', 'name' => $f->question,
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f->answer],
        ])->values(),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'FAQs', 'item' => route('faqs')],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
@include('partials.quiz-hero', [
    'heroTitle' => $page->block('hero_headline', 'Frequently Asked *Questions*'),
    'heroText' => $page->block('hero_sub', 'Quick answers about quotes, tours and planning your trip.'),
    'heroImage' => $page->block('hero_image', 'images/photos/city-leuven'),
    'heroAlt' => $page->block('hero_image_alt', ''),
    'crumb' => 'FAQs',
])

<section class="bg-ivory py-16 sm:py-24" aria-labelledby="fp-title">
    <div class="container-page grid gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:gap-16">
        <div>
            <p class="flex items-center gap-4 text-xs font-semibold uppercase tracking-[0.22em] text-slate">
                <span class="h-px w-10 bg-amber" aria-hidden="true"></span>FAQ
            </p>
            <h2 id="fp-title" class="mt-5 text-heading">@heading('Questions, Answered')</h2>
            @if($page->body)<div class="prose-page mt-6">{!! $page->body !!}</div>@endif

            <div class="on-dark mt-8 rounded-3xl bg-dusk p-6 text-cream sm:p-7">
                <h3 class="font-serif text-2xl font-normal text-cream">Still have questions?</h3>
                <p class="mt-2 max-w-sm text-[0.95rem] leading-relaxed text-cream/90">We are here to help with any queries. Reach out and get clear answers directly from our team.</p>
                <div class="mt-5 flex flex-wrap gap-3">
                    @if($site->phone)
                        <x-button variant="light" size="sm" :href="'tel:'.$site->phone_href">@icon('phone', 'size-4') Call us</x-button>
                    @endif
                    <x-button variant="cta" size="sm" icon="arrow-right" @click="$store.modal.open()">Get Quote Now</x-button>
                </div>
            </div>
        </div>

        <div>
            @if($faqs->isEmpty())
                <p class="text-lg text-slate">Answers to common questions are coming soon.</p>
            @else
                <div class="border-t border-ink/10">
                    @foreach($faqs as $f)
                        <details name="faq-page" @if($loop->first) open @endif class="group border-b border-ink/10 transition-colors open:rounded-2xl open:border-transparent open:bg-amber/20">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 rounded-2xl px-4 py-5 font-serif text-lg font-normal leading-snug text-ink sm:px-6 sm:text-xl [&::-webkit-details-marker]:hidden">
                                <span>{{ $f->question }}</span>
                                @icon('chevron-down', 'size-5 shrink-0 text-amber-ink transition-transform group-open:rotate-180')
                            </summary>
                            <div class="px-4 pb-6 text-[0.95rem] leading-relaxed text-ink/85 sm:px-6">{{ $f->answer }}</div>
                        </details>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
