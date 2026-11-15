@extends('layouts.app')

@section('title', $page->seo_title ?: $page->title)
@section('description', $page->meta_description)
@if($page->noindex)@section('noindex', '1')@endif

@section('content')
<section class="py-12 sm:py-16">
    <div class="container-page">
        <x-section-heading tag="h1" :title="$page->title" />
        <div class="prose-page mt-8">{!! $page->body !!}</div>

        <section id="photo-credits" class="mt-16 rounded-2xl bg-paper p-6 shadow-card" aria-labelledby="pc">
            <h2 id="pc" class="text-xl">Photo credits</h2>
            <p class="mt-2 text-slate">Photographs are used under Creative Commons licences from Wikimedia Commons. Full list, authors and licences:</p>
            <ul class="mt-3 space-y-1 text-sm text-slate">
                @foreach(config('credits.photos') as $c)
                    <li>{{ $c['title'] }}: <a class="underline" href="{{ $c['url'] }}" rel="noopener" target="_blank">{{ $c['author'] }}</a>, {{ $c['licence'] }}</li>
                @endforeach
            </ul>
        </section>
    </div>
</section>
@endsection
