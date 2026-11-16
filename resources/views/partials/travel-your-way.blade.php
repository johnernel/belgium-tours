{{-- Include with: tourTypes, eyebrow, heading --}}
@php $first = $tourTypes->first()?->slug; @endphp
<section class="py-16 sm:py-24" aria-labelledby="way-title" x-data="{ way: @js($first) }">
    <div class="container-page">
        <div class="max-w-3xl">
            <p class="flex items-center gap-4 text-xs font-semibold uppercase tracking-[0.22em] text-slate">
                <span class="h-px w-10 bg-amber" aria-hidden="true"></span>{{ $eyebrow }}
            </p>
            <h2 id="way-title" class="mt-5 text-heading">@heading($heading)</h2>
        </div>

        @if($tourTypes->isNotEmpty())
            <div class="mt-10 grid gap-6 lg:grid-cols-[0.85fr_1.15fr] lg:gap-14">
                {{-- Index list on desktop (hairline rows, number, serif title, dot/arrow), scrolling pill row on mobile. Real links to each tour page. --}}
                <div class="min-w-0">
                    <div class="mb-3 hidden items-center justify-between border-b border-ink/20 pb-4 text-xs font-semibold uppercase tracking-[0.22em] lg:flex">
                        <span class="text-slate">The index</span><span class="text-amber-ink">{{ ['', 'One', 'Two', 'Three', 'Four', 'Five'][min($tourTypes->count(), 5)] }} ways in</span>
                    </div>
                    <div class="-mx-4 overflow-x-auto px-4 [scrollbar-width:none] sm:-mx-6 sm:px-6 lg:mx-0 lg:overflow-visible lg:px-0 [&::-webkit-scrollbar]:hidden">
                        <div role="tablist" aria-label="Ways to travel" aria-orientation="vertical" class="flex w-max gap-2 lg:w-auto lg:flex-col lg:gap-0"
                             @keydown.down.prevent="$el.querySelector('[aria-selected=true]').nextElementSibling?.focus(); $el.querySelector('[aria-selected=true]').nextElementSibling?.click()"
                             @keydown.up.prevent="$el.querySelector('[aria-selected=true]').previousElementSibling?.focus(); $el.querySelector('[aria-selected=true]').previousElementSibling?.click()"
                             @keydown.right.prevent="$el.querySelector('[aria-selected=true]').nextElementSibling?.focus(); $el.querySelector('[aria-selected=true]').nextElementSibling?.click()"
                             @keydown.left.prevent="$el.querySelector('[aria-selected=true]').previousElementSibling?.focus(); $el.querySelector('[aria-selected=true]').previousElementSibling?.click()">
                            @foreach($tourTypes as $t)
                                <a id="wtab-{{ $t->slug }}" role="tab" href="{{ route('tours.show', $t->slug) }}" aria-controls="wpanel-{{ $t->slug }}"
                                   :aria-selected="way === '{{ $t->slug }}'" :tabindex="way === '{{ $t->slug }}' ? 0 : -1"
                                   @click.prevent="way = '{{ $t->slug }}'"
                                   class="group flex shrink-0 items-center gap-4 whitespace-nowrap rounded-full border-b-4 px-5 py-2 font-display text-xl leading-tight transition-colors lg:rounded-none lg:border-0 lg:border-b lg:border-ink/20 lg:px-1 lg:py-6 lg:text-[1.75rem]"
                                   :class="way === '{{ $t->slug }}' ? 'border-amber bg-sky-soft text-ink italic lg:bg-transparent' : 'border-transparent text-ink/60 hover:text-ink'">
                                    <span class="hidden w-6 font-sans text-xs not-italic tracking-widest text-ink/50 lg:inline">{{ sprintf('%02d', $loop->iteration) }}</span>
                                    <span class="lg:flex-1">{{ $t->name }}</span>
                                    <span class="hidden w-4 justify-center lg:flex" aria-hidden="true">
                                        <span x-show="way === '{{ $t->slug }}'" class="size-2 rounded-full bg-amber"></span>
                                        <span x-show="way !== '{{ $t->slug }}'" class="text-ink/50 opacity-0 transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100">@icon('arrow-up-right', 'size-4')</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Image with the description and link overlaid on a dark gradient --}}
                <div>
                    @foreach($tourTypes as $t)
                        <div id="wpanel-{{ $t->slug }}" role="tabpanel" aria-labelledby="wtab-{{ $t->slug }}" x-show="way === '{{ $t->slug }}'" @if(! $loop->first) style="display:none" @endif x-transition.opacity.duration.300ms
                             class="relative h-[280px] overflow-hidden rounded-[1.75rem] bg-ink shadow-card lg:h-[420px]">
                            @if($t->image)
                                <x-photo :base="$t->image" :alt="$t->image_alt ?: $t->name" sizes="(min-width:1024px) 55vw, 92vw" fetchpriority="low" class="absolute inset-0 size-full" :ratio="[1600, 1100]" />
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-transparent" aria-hidden="true"></div>
                            <div class="absolute inset-x-0 bottom-0 p-5 text-white sm:p-8">
                                <p class="max-w-xl text-base leading-relaxed text-white/90 sm:text-lg">{{ $t->summary }}</p>
                                <a href="{{ route('tours.show', $t->slug) }}" class="mt-3 inline-flex min-h-11 items-center gap-2 font-semibold text-white underline decoration-amber decoration-2 underline-offset-4 hover:text-amber">
                                    View tours<span class="sr-only"> for {{ $t->name }}</span> @icon('arrow-right', 'size-4')
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
