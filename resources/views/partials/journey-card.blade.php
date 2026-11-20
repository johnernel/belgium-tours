{{-- Include with: package, tag. Optional: solid (true = solid slate #58617E card for the other pages, default = frosted glass), level (heading level, default 3).
     On PHONES (below the sm breakpoint) the card is a horizontal layout copied from the client's reference: a tall photo on the left with the tour type in an amber tag,
     and on the right a text panel (nights + route label, title, From, price, a "More info" text link and a full-width "Get a Quote" button). All square corners on phones.
     From sm up nothing changes: the `contents` wrapper makes the text elements direct children of the card again. --}}
@php
    $days = $package->nights + 1;
    $symbol = ['EUR' => '€', 'USD' => '$', 'GBP' => '£'][$package->currency] ?? $package->currency.' ';
    $highlights = array_slice($package->highlights ?? [], 0, 3);
    $solid = $solid ?? false;
    $level = $level ?? 3;
    // Solid cards sit on #58617E, so the small text is a touch brighter to keep its contrast.
    $muted = $solid ? 'text-teal-deep' : 'text-cream/70';
    // Solid (light) card colours copied from the client's reference card: paper background, thin peach border, grey-green text, orange accents.
    $body = $solid ? 'text-ref-text' : 'text-cream';
    $desc = $solid ? 'text-ref-muted' : $muted.' sm:text-white';
    $priceCol = $solid ? 'text-ink' : 'text-amber';
    $square = $square ?? false; // true = no rounded corners (Homepage Test)
    // Tablet and desktop route line: "SELF DRIVE · Bruges · Ghent" (short tour type name, then the places)
    $typeLabel = ['self-drive' => 'Self Drive', 'chauffeur-driven' => 'Chauffeur', 'coach-tours' => 'Coach'][$package->tourType?->slug] ?? $package->tourType?->name;
    $nightsLabel = $package->nights > 0 ? $package->nights.' '.($package->nights === 1 ? 'night' : 'nights') : '1 day';
@endphp
{{-- Journey card. Frosted glass in the "Three Ways to Travel Belgium" carousel, solid slate everywhere else. --}}
<article class="journey-card group flex h-full flex-col {{ $square ? 'rounded-none' : 'rounded-[20px]' }} p-3 {{ $body }} shadow-lift max-sm:flex-row max-sm:rounded-none max-sm:p-0 sm:overflow-hidden sm:p-0 sm:transition-all sm:duration-300 sm:shadow-none sm:hover:-translate-y-1 {{ $solid ? 'border border-solid border-peach bg-card' : 'ring-1 bg-ink/55 ring-white/15 backdrop-blur-md' }}">
    <div class="relative aspect-[4/3] overflow-hidden {{ $square ? 'rounded-none' : 'rounded-[14px]' }} bg-slate sm:aspect-[3/2] sm:rounded-none max-sm:aspect-auto max-sm:w-[38%] max-sm:shrink-0 max-sm:rounded-none">
        @if($package->hero_image)
            <x-photo :base="$package->hero_image" :alt="$package->hero_image_alt ?: $package->title" sizes="(min-width:1024px) 30vw, (min-width:640px) 44vw, 40vw" fetchpriority="low"
                     class="size-full transition-transform duration-700 group-hover:scale-105 max-sm:absolute max-sm:inset-0" />
        @endif
        <span class="pointer-events-none absolute inset-x-0 bottom-0 h-2/5 bg-gradient-to-t from-ink/50 to-transparent max-sm:hidden" aria-hidden="true"></span>
        <span class="absolute left-3 top-3 rounded-full bg-ink/50 px-3 py-1 text-xs font-semibold text-cream backdrop-blur-md sm:left-4 sm:top-4 max-sm:hidden">{{ $days }} {{ $days === 1 ? 'day' : 'days' }}</span>
        <span class="absolute right-3 top-3 sm:right-4 sm:top-4 rounded-full {{ $solid ? 'bg-ink/70' : 'bg-sky/30' }} px-3 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.14em] text-white backdrop-blur-md sm:hidden max-sm:left-0 max-sm:right-auto max-sm:top-3 max-sm:rounded-none max-sm:bg-amber max-sm:px-3 max-sm:py-1.5 max-sm:text-ink max-sm:backdrop-blur-none">{{ $tag }}</span>

        {{-- Hover (and keyboard focus): three highlights marked with tiny amber dashes (not on phones) --}}
        @if($highlights)
            <div class="absolute inset-0 flex items-end bg-gradient-to-t from-ink/95 via-ink/70 to-ink/10 p-4 opacity-0 transition-opacity duration-300 group-focus-within:opacity-100 group-hover:opacity-100 max-sm:hidden">
                <ul class="space-y-1.5 text-sm text-cream">
                    @foreach($highlights as $h)
                        <li class="flex items-center gap-3"><span class="h-0.5 w-3 shrink-0 rounded-full bg-amber" aria-hidden="true"></span>{{ $h }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="sm:flex sm:min-w-0 sm:flex-1 sm:flex-col sm:px-6 sm:pb-6 sm:pt-5 max-sm:flex max-sm:min-w-0 max-sm:flex-1 max-sm:flex-col max-sm:px-5 max-sm:py-6">
        {{-- Phones only: "4 nights · route" label above the title, as in the reference --}}
        <p class="hidden text-[0.7rem] font-semibold uppercase leading-snug tracking-[0.18em] {{ $solid ? 'text-amber-ink' : 'text-amber-light' }} max-sm:block">{{ $nightsLabel }}@if($package->route) · {{ $package->route }}@endif</p>

        <h{{ $level }} class="mt-5 font-display text-xl leading-snug {{ $solid ? 'text-ref-text' : 'text-cream sm:text-white' }} sm:order-2 sm:mt-0 sm:text-[1.35rem] sm:leading-tight max-sm:mt-3 max-sm:text-[1.35rem]">{{ $package->title }}</h{{ $level }}>
        {{-- Tablet and desktop only: the short summary from Admin > Packages, at most 2 lines --}}
        @if(filled($package->summary))
            <p class="text-[0.92rem] leading-relaxed {{ $desc }} sm:order-3 sm:mt-2.5 sm:line-clamp-2 sm:min-h-[2.9rem] max-sm:hidden">{{ $package->summary }}</p>
        @endif
        @if($package->route || $typeLabel)
            <p class="mt-2.5 flex items-center gap-3 text-[0.7rem] font-semibold uppercase tracking-[0.16em] sm:order-1 sm:mb-2 sm:mt-0 sm:text-[0.62rem] sm:tracking-[0.16em] {{ $solid ? 'text-ref-text' : 'text-sky' }} max-sm:hidden">
                <span class="h-px w-5 shrink-0 {{ $solid ? 'bg-amber' : 'bg-sky/60' }}" aria-hidden="true"></span>{{ $typeLabel }}@if($typeLabel && $package->route) · @endif{{ $package->route }}
            </p>
        @endif

        <hr class="mt-4 {{ $solid ? 'border-peach-soft' : 'border-white/10' }} sm:order-4 sm:mt-4 max-sm:hidden">

        <div class="mt-auto pt-4 sm:order-5 sm:pt-5 max-sm:pt-4">
            @if($package->price_from !== null)
                {{-- One horizontal line: "From" label (or the crossed-out "From <old price>") beside the price, then "per person" (wraps only when the card is very narrow) --}}
                <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                    @if($package->original_price !== null && (float) $package->original_price > (float) $package->price_from)
                        <s class="text-sm font-semibold {{ $muted }}" aria-label="Was from {{ $symbol }}{{ number_format((float) $package->original_price) }}">From {{ $symbol }}{{ number_format((float) $package->original_price) }}</s>
                    @else
                        <span class="text-[0.7rem] font-semibold uppercase tracking-[0.14em] {{ $muted }}">From</span>
                    @endif
                    <span class="font-display text-3xl leading-none {{ $priceCol }} sm:text-[2.1rem] max-sm:text-[1.7rem]">{{ $symbol }}{{ number_format((float) $package->price_from) }}</span>
                    <span class="text-xs {{ $muted }} max-sm:hidden">per person</span>
                </div>
            @else
                <p class="font-display text-2xl leading-none {{ $priceCol }}">Price on request</p>
                <p class="mt-1 text-xs {{ $muted }} max-sm:hidden">Tailored to your dates</p>
            @endif

            {{-- Phones: "More info" becomes an underlined text link above a full-width quote button --}}
            <div class="mt-4 grid grid-cols-2 gap-2 sm:mt-5 sm:gap-3 max-sm:flex max-sm:flex-col-reverse max-sm:items-stretch max-sm:gap-4">
                <x-button variant="cta" size="sm" :square="$square" class="sm:!min-h-12 sm:text-[0.8rem] sm:font-bold sm:uppercase sm:tracking-[0.1em] max-sm:!rounded-none max-sm:!px-4 max-sm:uppercase max-sm:tracking-[0.14em]" @click="$store.modal.open({ packageId: '{{ $package->id }}', packageName: {{ \Illuminate\Support\Js::from($package->title) }}, tourType: {{ \Illuminate\Support\Js::from($package->tourType?->name) }} })">Get a Quote</x-button>
                <x-button variant="light" size="sm" :square="$square" :href="route('packages.show', $package->slug)" class="{{ $solid ? '!border-moss-line !text-moss hover:!bg-moss hover:!text-white' : '' }} sm:!min-h-12 sm:text-[0.8rem] sm:font-bold sm:uppercase sm:tracking-[0.1em] max-sm:!min-h-0 max-sm:!justify-start max-sm:!rounded-none max-sm:!border-0 max-sm:!bg-transparent max-sm:!p-0 max-sm:text-xs max-sm:uppercase max-sm:tracking-[0.18em] max-sm:underline max-sm:decoration-2 max-sm:underline-offset-4 max-sm:hover:!bg-transparent max-sm:hover:!text-amber">More Info<span class="sr-only"> about {{ $package->title }}</span></x-button>
            </div>
        </div>
    </div>
</article>