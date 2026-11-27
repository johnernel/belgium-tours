@extends('layouts.app')

@section('title', $page->seo_title ?: $page->title)
@section('description', (string) ($page->meta_description ?? ''))
@section('hero', '1')
@if($page->noindex)@section('noindex', '1')@endif

@section('content')
@include('partials.quiz-hero', [
    'heroTitle' => $page->block('hero_headline', 'About *Us*'),
    'heroText' => $page->block('hero_sub', 'How we plan self drive, chauffeur driven and coach tours of Belgium.'),
    'heroImage' => $page->block('hero_image', \App\Support\InfoPages::BANNERS['about'][0]),
    'heroAlt' => $page->block('hero_image_alt', \App\Support\InfoPages::BANNERS['about'][1]),
    'crumb' => 'About Us',
    'heroOneLine' => true,
])

@php $c = fn ($k) => $home->block($k); @endphp

<section class="py-12 sm:py-16">
    <div class="container-page">
        <div class="prose-page">{!! $page->body !!}</div>
    </div>
</section>

{{-- The same sections as on the home page, with the home page wording --}}
@include('partials.tailor-made')

@include('partials.travel-your-way', [
    'tourTypes' => $tourTypes,
    'eyebrow' => $c('way_eyebrow') ?: 'Pick your pace',
    'heading' => $c('way_heading') ?: 'Travel *Your Way*',
])

@include('partials.why-choose-us')

@include('partials.testimonials')

@include('partials.blog-section')

<section class="py-12 sm:py-16">
    <div class="container-page">
        <section id="photo-credits" class="rounded-2xl bg-paper p-6 shadow-card" aria-labelledby="pc">
            <h2 id="pc" class="text-xl">@heading('Photo credits')</h2>
            <p class="mt-2 text-slate">Photographs are used under Creative Commons licenses from Wikimedia Commons. Full list, authors and licenses:</p>
            <ul class="mt-3 space-y-1 text-sm text-slate">
                @foreach(config('credits.photos') as $credit)
                    <li>{{ $credit['title'] }}: <a class="underline" href="{{ $credit['url'] }}" rel="noopener" target="_blank">{{ $credit['author'] }}</a>, {{ $credit['licence'] }}</li>
                @endforeach
            </ul>
        </section>
    </div>
</section>
@endsection
