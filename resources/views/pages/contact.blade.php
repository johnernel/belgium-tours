@extends('layouts.app')

@section('title', $page->seo_title ?: $page->title)
@section('description', (string) ($page->meta_description ?? ''))
@section('hero', '1')
@if($page->noindex)@section('noindex', '1')@endif

@section('content')
@include('partials.page-hero', [
    'title' => $page->title,
    'sub' => $page->block('hero_sub', 'Ask a question or request a free quote. We reply as soon as we can.'),
    'image' => $page->block('hero_image', 'images/photos/brussels-grand-place'),
    'alt' => $page->block('hero_image_alt', 'Guildhouses of the Grand Place in Brussels lit at twilight'),
])

<section class="py-12 sm:py-16">
    <div class="container-page grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
        <div>
            <div class="prose-page">{!! $page->body !!}</div>
            <ul class="mt-6 space-y-3 text-lg">
                @if($site->phone)<li><a href="tel:{{ $site->phone_href }}" class="inline-flex min-h-11 items-center gap-2 font-semibold underline decoration-amber decoration-2 underline-offset-4">@icon('phone', 'size-5') {{ $site->phone }}</a></li>@endif
                @if($site->email)<li><a href="mailto:{{ $site->email }}" class="inline-flex min-h-11 items-center gap-2 font-semibold underline decoration-amber decoration-2 underline-offset-4">@icon('mail', 'size-5') {{ $site->email }}</a></li>@endif
                @if($site->address)<li class="flex gap-2">@icon('map-pin', 'mt-1 size-5 shrink-0') {{ $site->address }}</li>@endif
                @unless($site->phone || $site->email || $site->address)
                    <li class="rounded-xl bg-amber/30 p-4 text-base">[CONFIRM: phone, email and address. Add them in Admin &gt; Settings.]</li>
                @endunless
            </ul>
        </div>
        <div class="rounded-[1.75rem] bg-paper p-6 shadow-card sm:p-8">
            <h2 class="text-2xl">@heading('Send us a message')</h2>
            <div class="mt-5"><x-enquiry-form form-name="contact" :extended="true" id-prefix="c" submit-label="Send message" /></div>
        </div>
    </div>
</section>
@endsection
