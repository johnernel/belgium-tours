{{-- Include with: package, tag --}}
@php
    $days = $package->nights + 1;
    $symbol = ['EUR' => '€', 'USD' => '$', 'GBP' => '£'][$package->currency] ?? $package->currency.' ';
    $highlights = array_slice($package->highlights ?? [], 0, 3);
@endphp
{{-- Frosted-glass journey card for the "Three Ways to Travel Belgium" carousel. --}}
<article class="journey-card group flex h-full flex-col rounded-[20px] bg-ink/55 p-3 text-cream shadow-lift ring-1 ring-white/15 backdrop-blur-md">
    <div class="relative aspect-[4/3] overflow-hidden rounded-[14px] bg-slate">
        @if($package->hero_image)
            <x-photo :base="$package->hero_image" :alt="$package->hero_image_alt ?: $package->title" sizes="(min-width:1024px) 30vw, (min-width:640px) 44vw, 82vw" fetchpriority="low"
                     class="size-full transition-transform duration-700 group-hover:scale-105" />
        @endif
        <span class="absolute left-3 top-3 rounded-full bg-ink/50 px-3 py-1 text-xs font-semibold text-cream backdrop-blur-md">{{ $days }} {{ $days === 1 ? 'day' : 'days' }}</span>
        <span class="absolute right-3 top-3 rounded-full bg-sky/30 px-3 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.14em] text-white backdrop-blur-md">{{ $tag }}</span>

        {{-- Hover (and keyboard focus): three highlights marked with tiny amber dashes --}}
        @if($highlights)
            <div class="absolute inset-0 flex items-end bg-gradient-to-t from-ink/95 via-ink/70 to-ink/10 p-4 opacity-0 transition-opacity duration-300 group-focus-within:opacity-100 group-hover:opacity-100">
                <ul class="space-y-1.5 text-sm text-cream">
                    @foreach($highlights as $h)
                        <li class="flex items-center gap-3"><span class="h-0.5 w-3 shrink-0 rounded-full bg-amber" aria-hidden="true"></span>{{ $h }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <h3 class="mt-5 font-display text-xl leading-snug text-cream">{{ $package->title }}</h3>
    @if($package->route)
        <p class="mt-2.5 flex items-center gap-3 text-[0.7rem] font-semibold uppercase tracking-[0.16em] text-sky">
            <span class="h-px w-5 shrink-0 bg-sky/60" aria-hidden="true"></span>{{ $package->route }}
        </p>
    @endif

    <hr class="mt-4 border-white/15">

    <div class="mt-auto pt-4">
        @if($package->price_from !== null)
            <p class="text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-cream/70">From</p>
            <p class="font-display text-3xl leading-none text-amber">{{ $symbol }}{{ number_format((float) $package->price_from) }}</p>
            <p class="mt-1 text-xs text-cream/70">per person</p>
        @else
            <p class="font-display text-2xl leading-none text-amber">Price on request</p>
            <p class="mt-1 text-xs text-cream/70">Tailored to your dates</p>
        @endif

        <div class="mt-4 grid grid-cols-2 gap-2">
            <x-button variant="cta" size="sm" @click="$store.modal.open({ packageId: '{{ $package->id }}', packageName: {{ \Illuminate\Support\Js::from($package->title) }}, tourType: {{ \Illuminate\Support\Js::from($package->tourType?->name) }} })">Get a Quote</x-button>
            <x-button variant="light" size="sm" :href="route('packages.show', $package->slug)">More Info<span class="sr-only"> about {{ $package->title }}</span></x-button>
        </div>
    </div>
</article>
