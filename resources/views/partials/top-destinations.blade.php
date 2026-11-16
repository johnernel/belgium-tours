{{-- Include with: experiences (with packages_count), eyebrow, heading, cta --}}
@php
    $nTours = fn ($n) => $n === 1 ? '1 tour' : $n.' tours';
@endphp
<section class="py-16 sm:py-24" aria-labelledby="dest-title" x-data="{ open: 0 }">
    <div class="container-page">
        <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
            <div class="max-w-3xl">
                <p class="flex items-center gap-4 text-xs font-semibold uppercase tracking-[0.22em] text-slate">
                    <span class="h-px w-10 bg-amber" aria-hidden="true"></span>{{ $eyebrow }}
                </p>
                <h2 id="dest-title" class="mt-5 text-heading">@heading($heading)</h2>
            </div>
            <div class="shrink-0">
                <x-button variant="cta" size="lg" icon="arrow-right" :href="route('destinations.index')">{{ $cta }}</x-button>
            </div>
        </div>

        @if($experiences->isNotEmpty())
            {{-- Expanding accordion: horizontal on desktop (hover or click a strip), vertical stack on mobile (tap a panel) --}}
            <div class="mt-10 flex flex-col gap-3 lg:h-[35rem] lg:flex-row lg:gap-3.5">
                @foreach($experiences as $i => $e)
                    <article class="relative isolate overflow-hidden rounded-[1.75rem] bg-ink text-white transition-[height,flex-grow] duration-500 ease-out lg:h-auto lg:min-w-0 lg:basis-0"
                             :class="open === {{ $i }} ? 'h-[360px] lg:grow-[4.6]' : 'h-20 lg:grow'"
                             @mouseenter="if (window.matchMedia('(min-width: 1024px)').matches) open = {{ $i }}">
                        @if($e->image)
                            <x-photo :base="$e->image" :alt="$e->image_alt ?: $e->name" :widths="[480, 800, 1280, 1600]" sizes="(min-width:1024px) 640px, 400px" fetchpriority="low" :ratio="[1600, 1067]"
                                     class="absolute inset-0 -z-10 size-full" style="object-position: {{ $e->image_focus ?: '50% 50%' }}" />
                        @endif
                        <div class="absolute inset-0 -z-10 transition-colors duration-500"
                             :class="open === {{ $i }} ? 'bg-gradient-to-t from-ink/85 via-ink/15 to-transparent' : 'bg-ink/45'"></div>

                        {{-- The whole panel is a button that opens it (hover, tap, click or keyboard focus) --}}
                        <button type="button" class="absolute inset-0 z-10 cursor-pointer focus-visible:outline-offset-[-4px]" :class="open === {{ $i }} && 'pointer-events-none'"
                                :aria-expanded="open === {{ $i }}" @click="open = {{ $i }}" @focus="open = {{ $i }}">
                            <span class="sr-only">Show {{ $e->name }}, {{ $nTours($e->packages_count) }}</span>
                        </button>

                        {{-- Collapsed label: vertical on desktop, a single line on mobile --}}
                        <span class="pointer-events-none absolute font-display text-xl transition-opacity duration-300 max-lg:left-6 max-lg:top-1/2 max-lg:-translate-y-1/2 lg:bottom-7 lg:left-1/2 lg:-translate-x-1/2 lg:rotate-180 lg:text-2xl lg:[writing-mode:vertical-rl]"
                              :class="open === {{ $i }} ? 'opacity-0' : 'opacity-100'" aria-hidden="true">{{ $e->name }}</span>

                        {{-- Expanded content --}}
                        <div class="absolute inset-x-0 bottom-0 z-20 p-6 transition duration-500 lg:p-8" :class="open === {{ $i }} ? 'translate-y-0 opacity-100' : 'pointer-events-none translate-y-3 opacity-0'" :inert="open !== {{ $i }}">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky">{{ $nTours($e->packages_count) }}</p>
                            <h3 class="mt-2 font-display text-4xl text-white lg:text-[2.75rem]">{{ $e->name }}</h3>
                            @if($e->description)<p class="mt-2 max-w-[calc(100%-4.5rem)] text-sm text-white/85 sm:max-w-[calc(100%-5rem)] sm:text-base lg:max-w-[17rem]">{{ $e->description }}</p>@endif
                            <a href="{{ route('packages.index', ['experience' => $e->slug]) }}" aria-label="View {{ $e->name }} tours"
                               class="absolute bottom-6 right-6 grid size-12 place-items-center rounded-full bg-amber text-ink shadow-lift transition hover:bg-sky-hover lg:bottom-8 lg:right-8">@icon('arrow-right', 'size-5')</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
