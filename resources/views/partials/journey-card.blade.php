{{-- Include with: package, tag. Optional: solid (true = solid slate #58617E card for the other pages, default = frosted glass), level (heading level, default 3). --}}
@php
    $days = $package->nights + 1;
    $symbol = ['EUR' => '€', 'USD' => '$', 'GBP' => '£'][$package->currency] ?? $package->currency.' ';
    $highlights = array_slice($package->highlights ?? [], 0, 3);
    $solid = $solid ?? false;
    $level = $level ?? 3;
    // Solid cards sit on #58617E, so the small text is a touch brighter to keep its contrast.
    $muted = $solid ? 'text-cream/90' : 'text-cream/70';
    $square = $square ?? false; // true = no rounded corners (Homepage Test)
@endphp
{{-- Journey card. Frosted glass in the "Three Ways to Travel Belgium" carousel, solid slate everywhere else. --}}
<article class="journey-card group flex h-full flex-col {{ $square ? 'rounded-none' : 'rounded-[20px]' }} p-3 text-cream shadow-lift ring-1 {{ $solid ? 'bg-slate ring-white/10' : 'bg-ink/55 ring-white/15 backdrop-blur-md' }}">
    <div class="relative aspect-[4/3] overflow-hidden {{ $square ? 'rounded-none' : 'rounded-[14px]' }} bg-slate">
        @if($package->hero_image)
            <x-photo :base="$package->hero_image" :alt="$package->hero_image_alt ?: $package->title" sizes="(min-width:1024px) 30vw, (min-width:640px) 44vw, 82vw" fetchpriority="low"
                     class="size-full transition-transform duration-700 group-hover:scale-105" />
        @endif
        <span class="absolute left-3 top-3 rounded-full bg-ink/50 px-3 py-1 text-xs font-semibold text-cream backdrop-blur-md">{{ $days }} {{ $days === 1 ? 'day' : 'days' }}</span>
        <span class="absolute right-3 top-3 rounded-full {{ $solid ? 'bg-ink/70' : 'bg-sky/30' }} px-3 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.14em] text-white backdrop-blur-md">{{ $tag }}</span>

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

    <h{{ $level }} class="mt-5 font-display text-xl leading-snug text-cream">{{ $package->title }}</h{{ $level }}>
    @if($package->route)
        <p class="mt-2.5 flex items-center gap-3 text-[0.7rem] font-semibold uppercase tracking-[0.16em] {{ $solid ? 'text-sky-soft' : 'text-sky' }}">
            <span class="h-px w-5 shrink-0 {{ $solid ? 'bg-sky-soft/60' : 'bg-sky/60' }}" aria-hidden="true"></span>{{ $package->route }}
        </p>
    @endif

    <hr class="mt-4 border-white/15">

    <div class="mt-auto pt-4">
        @if($package->price_from !== null)
            @if($package->original_price !== null && (float) $package->original_price > (float) $package->price_from)
                {{-- Reduced price: "From" and the original price crossed out, on their own line above the current price --}}
                <p class="mb-1"><s class="text-sm font-semibold {{ $muted }}" aria-label="Was from {{ $symbol }}{{ number_format((float) $package->original_price) }}">From {{ $symbol }}{{ number_format((float) $package->original_price) }}</s></p>
            @endif
            <p class="text-[0.65rem] font-semibold uppercase tracking-[0.18em] {{ $muted }}">From</p>
            <p class="font-display text-3xl leading-none text-amber">{{ $symbol }}{{ number_format((float) $package->price_from) }}</p>
            <p class="mt-1 text-xs {{ $muted }}">per person</p>        @else
            <p class="font-display text-2xl leading-none text-amber">Price on request</p>
            <p class="mt-1 text-xs {{ $muted }}">Tailored to your dates</p>
        @endif

        <div class="mt-4 grid grid-cols-2 gap-2">
            <x-button variant="cta" size="sm" :square="$square" @click="$store.modal.open({ packageId: '{{ $package->id }}', packageName: {{ \Illuminate\Support\Js::from($package->title) }}, tourType: {{ \Illuminate\Support\Js::from($package->tourType?->name) }} })">Get a Quote</x-button>
            <x-button variant="light" size="sm" :square="$square" :href="route('packages.show', $package->slug)">More Info<span class="sr-only"> about {{ $package->title }}</span></x-button>
        </div>
    </div>
</article>
