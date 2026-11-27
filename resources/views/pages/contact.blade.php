@extends('layouts.app')

@section('title', $page->seo_title ?: $page->title)
@section('description', (string) ($page->meta_description ?? ''))
@section('hero', '1')
@section('footer_flush', '1')
@if($page->noindex)@section('noindex', '1')@endif

@section('content')
{{-- Same photo hero as the other pages: headline on the left, the main quiz form on the right --}}
@include('partials.quiz-hero', [
    'heroTitle' => $page->title,
    'heroText' => $page->block('hero_sub', 'Ask a question or request a free quote. We reply as soon as we can.'),
    'heroImage' => $page->block('hero_image', \App\Support\InfoPages::BANNERS['contact'][0]),
    'heroAlt' => $page->block('hero_image_alt', \App\Support\InfoPages::BANNERS['contact'][1]),
    'crumb' => $page->title,
    'heroOneLine' => true,
])

@php
    // Contact details: the ones saved in Admin > Settings win; until they are set, the same placeholders as the header strip are shown.
    $details = array_values(array_filter([
        ['phone', 'Call us', $site->phone ?: '+1 000-000-0000', 'tel:'.($site->phone ? $site->phone_href : '+10000000000')],
        ['mail', 'Email us', $site->email ?: 'info@gobelgiumtrips.com', 'mailto:'.($site->email ?: 'info@gobelgiumtrips.com')],
        $site->address ? ['map-pin', 'Find us', $site->address, null] : null,
    ]));
@endphp

{{-- Contact details on the left, the message form on the right. Everything here is square (no border radius). --}}
<section class="bg-white py-[90px]" aria-labelledby="contact-details-title">
    <div class="container-wide grid gap-8 lg:grid-cols-[0.85fr_1.15fr] lg:items-stretch [&_*]:!rounded-none">
        <div class="relative isolate flex flex-col overflow-hidden bg-sky p-6 text-ink sm:p-10">
            <img src="{{ asset('images/decor/belgium-outline.webp') }}" alt="" width="1500" height="1000" loading="lazy" decoding="async" class="pointer-events-none absolute -bottom-2 -right-10 -z-10 h-auto w-[min(30rem,100%)] opacity-[0.22] [mask-image:linear-gradient(to_top,#000_55%,transparent)]" aria-hidden="true">

            <p class="flex items-center gap-4 text-xs font-semibold uppercase tracking-[0.22em] text-ink"><span class="h-px w-10 bg-amber" aria-hidden="true"></span>Get in touch</p>
            <h2 id="contact-details-title" class="mt-4 text-[2rem] leading-[1.1] sm:text-[2.5rem]">@heading('Contact *Details*')</h2>
            @if(filled($page->body))<div class="prose-page mt-4">{!! $page->body !!}</div>@endif

            <ul class="mt-6 space-y-3">
                @foreach($details as [$icon, $label, $value, $href])
                    <li class="flex items-center gap-4 bg-white p-4 shadow-card">
                        <span class="grid size-12 shrink-0 place-items-center bg-amber/20 text-amber-ink" aria-hidden="true">@icon($icon, 'size-6')</span>
                        <div class="min-w-0">
                            <span class="block text-xs font-semibold uppercase tracking-[0.18em] text-slate">{{ $label }}</span>
                            @if($href)
                                <a href="{{ $href }}" class="inline-flex min-h-8 items-center break-all text-lg font-semibold text-ink hover:text-amber-ink">{{ $value }}</a>
                            @else
                                <span class="block text-lg font-semibold leading-snug">{{ $value }}</span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            <ul class="mt-8 space-y-2.5 border-t border-ink/15 pt-6 text-sm">
                @foreach(['Free to request, no obligation', 'Your details stay private', 'No spam, ever'] as $point)
                    <li class="flex items-center gap-3"><span class="grid size-5 shrink-0 place-items-center bg-amber-ink text-white" aria-hidden="true">@icon('check', 'size-3.5')</span>{{ $point }}</li>
                @endforeach
            </ul>
        </div>

        <div class="border border-ink/10 bg-paper p-6 shadow-card sm:p-10">
            <h2 class="text-[2rem] leading-[1.1] sm:text-[2.5rem]">@heading('Send us a *message*')</h2>
            <div class="mt-6"><x-enquiry-form form-name="contact" :extended="true" id-prefix="c" submit-label="Send message" submit-variant="cta" /></div>
        </div>
    </div>
</section>
@endsection
