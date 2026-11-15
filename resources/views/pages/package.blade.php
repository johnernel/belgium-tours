@extends('layouts.app')

@section('title', $package->seo_title ?: $package->title.' | '.$site->brand)
@section('description', $package->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $package->summary), 155))
@if($package->noindex)@section('noindex', '1')@endif
@if($package->social_image)@section('og_image', asset($package->social_image))@elseif($package->hero_image)@section('og_image', asset($package->hero_image.'-1600.webp'))@endif

@push('schema')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Packages', 'item' => route('packages.index')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $package->title, 'item' => route('packages.show', $package->slug)],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    <script type="application/ld+json">{!! json_encode(array_filter([
        '@context' => 'https://schema.org', '@type' => 'TouristTrip',
        'name' => $package->title,
        'description' => strip_tags((string) $package->summary),
        'image' => $package->hero_image ? asset($package->hero_image.'-1600.webp') : null,
        'touristType' => $package->tourType?->name,
        'provider' => ['@type' => 'TravelAgency', 'name' => $site->brand],
        'offers' => $package->price_from ? ['@type' => 'Offer', 'price' => (string) $package->price_from, 'priceCurrency' => $package->currency, 'availability' => 'https://schema.org/InStock'] : null,
    ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @if($package->faqs)
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org', '@type' => 'FAQPage',
        'mainEntity' => collect($package->faqs)->map(fn ($f) => ['@type' => 'Question', 'name' => $f['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['answer']]])->values(),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endif
@endpush

@section('content')
<section class="relative isolate overflow-hidden bg-ink text-white">
    @if($package->hero_image)
        <x-photo :base="$package->hero_image" :alt="$package->hero_image_alt ?: $package->title" :eager="true" class="absolute inset-0 z-0 size-full" />
    @endif
    <div class="absolute inset-0 z-[1] bg-gradient-to-t from-ink via-ink/60 to-ink/30"></div>
    <div class="container-page relative z-10 py-14 sm:py-24">
        <nav aria-label="Breadcrumb" class="mb-4 text-sm text-white/80"><a href="{{ route('home') }}" class="underline">Home</a> / <a href="{{ route('packages.index') }}" class="underline">Packages</a></nav>
        <h1 class="max-w-3xl text-4xl !text-white sm:text-5xl">{{ $package->title }}</h1>
        <ul class="mt-5 flex flex-wrap gap-2 text-sm font-bold text-ink">
            <li class="rounded-full bg-amber px-3 py-1.5">{{ $package->nights }} nights</li>
            @if($package->tourType)<li class="rounded-full bg-sky px-3 py-1.5">{{ $package->tourType->name }}</li>@endif
            @if($package->destination)<li class="rounded-full bg-cream px-3 py-1.5">{{ $package->destination->name }}</li>@endif
        </ul>
    </div>
</section>

<div class="container-page grid gap-10 py-12 lg:grid-cols-[1fr_22rem] lg:py-16">
    <div class="min-w-0 space-y-12">
        @if($package->summary)<p class="text-xl text-slate">{{ $package->summary }}</p>@endif

        @if($package->highlights)
            <section aria-labelledby="hl"><h2 id="hl" class="text-2xl sm:text-3xl">Highlights</h2>
                <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach($package->highlights as $h)
                        <li class="flex gap-3 rounded-2xl bg-paper p-4 shadow-card"><x-icon name="check" class="mt-0.5 size-5 shrink-0 text-amber-deep" /> <span>{{ $h }}</span></li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if($package->description)
            <section aria-labelledby="ov"><h2 id="ov" class="text-2xl sm:text-3xl">Overview</h2>
                <div class="prose-page mt-4">{!! $package->description !!}</div>
            </section>
        @endif

        @if($package->itinerary)
            <section aria-labelledby="it"><h2 id="it" class="text-2xl sm:text-3xl">Day by day</h2>
                <div class="mt-4 space-y-3">
                    @foreach($package->itinerary as $day)
                        <x-faq-item :question="$day['title']">{{ $day['text'] }}</x-faq-item>
                    @endforeach
                </div>
            </section>
        @endif

        @if($package->inclusions || $package->exclusions)
            <section class="grid gap-6 sm:grid-cols-2">
                @if($package->inclusions)
                    <div class="rounded-2xl bg-sky-soft p-6"><h2 class="text-xl">Included</h2><ul class="mt-3 space-y-2">@foreach($package->inclusions as $i)<li class="flex gap-2"><x-icon name="check" class="mt-1 size-4 shrink-0" />{{ $i }}</li>@endforeach</ul></div>
                @endif
                @if($package->exclusions)
                    <div class="rounded-2xl bg-cream-deep p-6"><h2 class="text-xl">Not included</h2><ul class="mt-3 space-y-2">@foreach($package->exclusions as $i)<li class="flex gap-2"><x-icon name="x" class="mt-1 size-4 shrink-0" />{{ $i }}</li>@endforeach</ul></div>
                @endif
            </section>
        @endif

        @if($package->images->isNotEmpty())
            <section aria-labelledby="gal"><h2 id="gal" class="text-2xl sm:text-3xl">Gallery</h2>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach($package->images as $img)
                        <x-photo :base="$img->path" :alt="$img->alt" sizes="(min-width:640px) 30vw, 46vw" class="aspect-[4/3] w-full rounded-2xl" />
                    @endforeach
                </div>
            </section>
        @endif

        @if($package->faqs)
            <section aria-labelledby="pf"><h2 id="pf" class="text-2xl sm:text-3xl">Questions about this trip</h2>
                <div class="mt-4 space-y-3">@foreach($package->faqs as $f)<x-faq-item :question="$f['question']">{{ $f['answer'] }}</x-faq-item>@endforeach</div>
            </section>
        @endif
    </div>

    <aside class="lg:sticky lg:top-24 lg:self-start" aria-label="Request a quote">
        <div class="rounded-[1.75rem] border-4 border-amber bg-paper p-6 shadow-lift">
            <p class="font-display text-2xl font-semibold">{{ $package->priceLabel() }}</p>
            <p class="text-sm text-slate">per person, based on the trip as described</p>
            <h2 class="mt-5 text-xl">Get a free quote</h2>
            <div class="mt-4">
                <x-enquiry-form form-name="package-quote" :package="$package" id-prefix="p" submit-label="Get a quote for this trip" />
            </div>
        </div>
        <button type="button" class="mt-4 w-full rounded-full border-2 border-ink px-6 py-3 font-display font-semibold hover:bg-ink hover:text-cream"
                @click="$store.modal.open({ mode: 'question', packageId: '{{ $package->id }}', packageName: @js($package->title) })">Ask us a question</button>
    </aside>
</div>

@if($related->isNotEmpty())
    <section class="pb-8"><div class="container-page"><h2 class="text-2xl sm:text-3xl">You might also like</h2>
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">@foreach($related as $package)@include('partials.package-card', ['package' => $package])@endforeach</div>
    </div></section>
@endif
@endsection
