{{-- Include with: tourTypes (with packages), eyebrow, heading, sub --}}
@php
    // Short labels and icons for the tabs; types added later fall back to their name and a compass.
    $tabIcons = ['self-drive' => 'steering-wheel', 'chauffeur-driven' => 'chauffeur', 'coach-tours' => 'coach'];
    $tabs = $tourTypes->values();
    $first = $tabs->first()?->slug;
@endphp
{{-- The only video-background section: a looping clip under a charcoal overlay with soft top and bottom vignettes. --}}
<section class="relative isolate overflow-hidden bg-ink py-20 text-cream sm:py-28" aria-labelledby="ways-title" x-data="{ tab: @js($first) }">
    <div class="absolute inset-0 -z-0" aria-hidden="true">
        <video class="absolute inset-0 size-full object-cover" muted loop playsinline preload="none" tabindex="-1"
               data-poster="{{ asset('media/ways-poster.webp') }}" data-lazy-video
               data-webm="{{ asset('media/ways-loop.webm') }}" data-mp4="{{ asset('media/ways-loop.mp4') }}"></video>
        <div class="absolute inset-0 bg-ink/75"></div>
        <div class="absolute inset-x-0 top-0 h-48 bg-gradient-to-b from-ink to-transparent"></div>
        <div class="absolute inset-x-0 bottom-0 h-48 bg-gradient-to-t from-ink to-transparent"></div>
    </div>

    <div class="container-page relative z-10">
        <div class="mx-auto max-w-3xl text-center">
            <p class="flex items-center justify-center gap-4 text-xs font-semibold uppercase tracking-[0.22em] text-sky">
                <span class="h-px w-10 bg-amber" aria-hidden="true"></span>{{ $eyebrow }}
            </p>
            <h2 id="ways-title" class="on-dark mt-5 text-heading !text-cream">@heading($heading)</h2>
            @if($sub)<p class="mt-4 text-lg text-cream/85">{{ $sub }}</p>@endif
        </div>

        {{-- Tabs: links to the tour type pages that become a tab switcher when JavaScript runs --}}
        <div class="mx-auto mt-9 flex max-w-full justify-start overflow-x-auto px-1 py-1 [scrollbar-width:none] sm:justify-center [&::-webkit-scrollbar]:hidden">
            <div role="tablist" aria-label="Ways to travel" class="flex shrink-0 gap-1.5 rounded-full bg-white/10 p-1 ring-1 ring-white/15 backdrop-blur-md sm:gap-3 sm:bg-transparent sm:p-0 sm:ring-0 sm:backdrop-blur-none"
                 @keydown.right.prevent="$el.querySelector('[aria-selected=true]').nextElementSibling?.focus(); $el.querySelector('[aria-selected=true]').nextElementSibling?.click()"
                 @keydown.left.prevent="$el.querySelector('[aria-selected=true]').previousElementSibling?.focus(); $el.querySelector('[aria-selected=true]').previousElementSibling?.click()">
                @foreach($tabs as $t)
                    <a id="tab-{{ $t->slug }}" role="tab" href="{{ route('tours.show', $t->slug) }}" aria-controls="panel-{{ $t->slug }}"
                       :aria-selected="tab === '{{ $t->slug }}'" :tabindex="tab === '{{ $t->slug }}' ? 0 : -1"
                       @click.prevent="tab = '{{ $t->slug }}'"
                       class="inline-flex min-h-11 items-center gap-2 whitespace-nowrap rounded-full px-5 py-2.5 text-sm font-semibold transition-colors sm:px-6 sm:ring-1 sm:backdrop-blur-md"
                       :class="tab === '{{ $t->slug }}' ? 'bg-amber text-ink sm:ring-amber' : 'text-cream hover:bg-white/10 sm:bg-white/10 sm:ring-white/25'">
                        @icon($tabIcons[$t->slug] ?? 'compass', 'size-4 shrink-0'){{ $t->tab_label ?: $t->name }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- One carousel per tab --}}
        <div class="mt-10">
            @foreach($tabs as $t)
                <div id="panel-{{ $t->slug }}" role="tabpanel" aria-labelledby="tab-{{ $t->slug }}" x-show="tab === '{{ $t->slug }}'" @if(! $loop->first) style="display:none" @endif
                     x-data="carousel()" x-transition.opacity.duration.300ms>
                    @if($t->packages->isEmpty())
                        <p class="mx-auto max-w-xl rounded-2xl bg-ink/55 p-6 text-center ring-1 ring-white/15 backdrop-blur-md">We are adding {{ strtolower($t->name) }} journeys. <a class="font-semibold underline decoration-amber decoration-2 underline-offset-4" href="{{ route('tours.show', $t->slug) }}">See {{ $t->name }}</a> or ask us to plan one.</p>
                    @else
                        <div class="relative">
                            <div x-ref="track" @scroll.passive="update()" tabindex="0" aria-label="{{ $t->tab_label ?: $t->name }} journeys"
                                 class="flex snap-x snap-mandatory items-stretch gap-4 overflow-x-auto scroll-smooth pb-2 [scrollbar-width:none] motion-reduce:scroll-auto [&::-webkit-scrollbar]:hidden">
                                @foreach($t->packages as $package)
                                    <div class="w-[82%] shrink-0 snap-start sm:w-[calc((100%-1rem)/2.25)] lg:w-[calc((100%-3rem)/3.25)]">
                                        @include('partials.journey-card', ['package' => $package, 'tag' => $t->tab_label ?: $t->name])
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" @click="prev()" :disabled="!canPrev" aria-label="Previous journeys"
                                    class="absolute -left-5 top-[22%] z-10 hidden size-11 place-items-center rounded-full bg-amber text-ink shadow-lift transition hover:bg-sky-hover disabled:cursor-default disabled:opacity-40 disabled:hover:bg-amber lg:grid xl:-left-6">@icon('chevron-down', 'size-5 rotate-90')</button>
                            <button type="button" @click="next()" :disabled="!canNext" aria-label="Next journeys"
                                    class="absolute -right-5 top-[22%] z-10 hidden size-11 place-items-center rounded-full bg-amber text-ink shadow-lift transition hover:bg-sky-hover disabled:cursor-default disabled:opacity-40 disabled:hover:bg-amber lg:grid xl:-right-6">@icon('chevron-down', 'size-5 -rotate-90')</button>
                        </div>

                        <div class="mt-5 flex items-center justify-center" role="group" aria-label="Choose a journey">
                            @foreach($t->packages as $i => $package)
                                <button type="button" @click="go({{ $i }})" aria-label="Go to journey {{ $i + 1 }}: {{ $package->title }}"
                                        :aria-current="active === {{ $i }}" class="group grid h-6 min-w-6 place-items-center px-0.5">
                                    <span class="block h-2 rounded-full transition-all duration-300"
                                          :class="active === {{ $i }} ? 'w-7 bg-amber' : 'w-2 bg-white/35 group-hover:bg-white/60'"></span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <p class="mt-8 text-center">
            <a href="{{ route('packages.index') }}" class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold uppercase tracking-[0.16em] text-cream hover:text-amber">View all journeys @icon('arrow-right', 'size-4')</a>
        </p>
    </div>
</section>
