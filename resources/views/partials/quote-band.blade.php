{{-- Quote band: full-width photo, dark overlay, centred copy. Include inside the home page (uses the $c content closure). --}}
@php
    $styles = array_values(array_filter(array_map('trim', explode(',', (string) $c('qb_styles')))));
@endphp
<section class="on-dark relative isolate overflow-hidden py-20 text-center text-white sm:py-28 lg:py-32" aria-labelledby="qb-title">
    <x-photo base="images/photos/brussels-grand-place" alt="" :widths="[800, 1280, 1600]" sizes="100vw" fetchpriority="low" :ratio="[3840, 2858]" class="absolute inset-0 -z-20 size-full" />
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-ink/70 via-ink/60 to-ink/80" aria-hidden="true"></div>

    <div class="container-page">
        <h2 id="qb-title" class="mx-auto max-w-3xl text-heading text-white">@heading($c('qb_heading') ?: 'Belgium, *your way*')</h2>
        <p class="mx-auto mt-5 max-w-2xl font-serif text-2xl font-light text-white sm:text-[1.65rem]">{{ $c('qb_sub') }}</p>
        <p class="mx-auto mt-4 max-w-xl text-lg leading-relaxed text-white/90">{{ $c('qb_text') }}</p>

        @if($styles)
            <ul class="mt-7 flex flex-wrap justify-center gap-3" aria-label="Travel styles">
                @foreach($styles as $s)
                    <li class="rounded-full border border-white/40 px-4 py-1.5 text-sm font-semibold text-white">{{ $s }}</li>
                @endforeach
            </ul>
        @endif

        <div class="mt-9 flex flex-wrap justify-center gap-4">
            <x-button variant="cta" size="lg" icon="arrow-right" @click="$store.modal.open()">{{ $c('qb_cta') ?: 'Plan my trip' }}</x-button>
            <x-button variant="light" size="lg" :href="route('packages.index')">{{ $c('qb_cta2') ?: 'See Packages' }}</x-button>
        </div>
    </div>
</section>
