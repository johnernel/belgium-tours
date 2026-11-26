@extends('layouts.app')

@section('title', $style->seo_title ?: $style->name.': a Belgium travel style | '.$site->brand)
@section('description', $style->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) ($style->intro ?: $style->description)), 155))
@section('hero', '1')
@if($style->image)@section('og_image', asset($style->image.'-1600.webp'))@endif

@push('schema')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Travel styles', 'item' => route('travel-styles.index')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $style->name, 'item' => route('travel-styles.show', $style->slug)],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('footer_flush', '1')

@section('content')
@include('partials.quiz-hero', [
    'heroTitle' => $style->name,
    'heroText' => $style->description,
    'heroImage' => $style->image,
    'heroAlt' => $style->image_alt ?: $style->name,
    'crumbMid' => [route('travel-styles.index'), 'Travel styles'],
    'crumb' => $style->name,
])

{{-- ABOUT THE STYLE --}}
<section class="bg-white py-16 sm:py-24" aria-labelledby="style-about-title">
    <div class="container-page text-center">
        <div class="mx-auto max-w-3xl">
            @include('partials.ht-eyebrow', ['label' => 'Travel style', 'center' => true])
            <h2 id="style-about-title" class="mt-5 text-heading">@heading('Belgium for *'.\Illuminate\Support\Str::lower($style->name).'*')</h2>
            <p class="mt-6 text-lg leading-relaxed text-ink/90">{{ $style->intro ?: $style->description }}</p>
            <div class="mt-8"><x-button square variant="cta" size="lg" icon="arrow-right" @click="$store.modal.open()">Get a free quote</x-button></div>
        </div>
    </div>
</section>

{{-- TRIPS OF THIS STYLE --}}
<section class="bg-ivory py-16 sm:py-24" aria-labelledby="style-trips-title">
    <div class="container-wide">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <x-section-heading id="style-trips-title" :title="$style->name.' trips'" sub="Sample trips to start from. Every one can be changed to fit your dates, pace and budget." />
            <x-button square :href="route('packages.index')" variant="outline" icon="arrow-right" class="hover:!border-cta-hover hover:!bg-cta-hover hover:!text-white">All packages</x-button>
        </div>
        @if($packages->isEmpty())
            <p class="mt-10 bg-paper p-6 shadow-card">We are adding {{ \Illuminate\Support\Str::lower($style->name) }} trips. In the meantime <button type="button" class="font-semibold underline" @click="$store.modal.open()">tell us what you have in mind</button> and we will plan one for you.</p>
        @else
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($packages as $package)@include('partials.package-card', ['package' => $package, 'level' => 3])@endforeach
            </div>
        @endif
    </div>
</section>

{{-- OTHER TRAVEL STYLES --}}
@if($others->isNotEmpty())
<section class="bg-white py-16 sm:py-24" aria-labelledby="style-more-title">
    <div class="container-wide">
        <h2 id="style-more-title" class="text-heading">@heading('Other ways to *see Belgium*')</h2>
        <div class="mt-10">
            @include('partials.place-tiles', ['label' => 'Other travel styles', 'items' => $others->map(fn ($o) => ['name' => $o->name, 'url' => route('travel-styles.show', $o->slug), 'image' => $o->image, 'focus' => $o->image_focus, 'sub' => $o->packages_count === 1 ? '1 tour' : $o->packages_count.' tours'])->all()])
        </div>
    </div>
</section>
@endif

@include('partials.closing-band', ['label' => $style->name, 'heading' => 'Ready for *'.\Illuminate\Support\Str::lower($style->name).'?*', 'modalType' => ''])
@endsection
