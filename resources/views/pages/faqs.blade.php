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
    'heroImage' => $page->block('hero_image', \App\Support\InfoPages::BANNERS['faqs'][0]),
    'heroAlt' => $page->block('hero_image_alt', ''),
    'crumb' => 'FAQs',
])

@include('partials.faq-band', ['faqs' => $faqs, 'body' => $page->body])

@endsection
