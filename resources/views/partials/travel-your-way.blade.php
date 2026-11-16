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
            <div class="mt-10 grid gap-8 lg:grid-cols-[0.85fr_1.15fr] lg:gap-14">
                {{-- Tabs: a vertical list on desktop, a scrolling row on mobile. They are real links to each tour page. --}}
                <div class="-mx-4 overflow-x-auto px-4 [scrollbar-width:none] sm:-mx-6 sm:px-6 lg:mx-0 lg:overflow-visible lg:px-0 [&::-webkit-scrollbar]:hidden">
                    <div role="tablist" aria-label="Ways to travel" aria-orientation="vertical" class="flex w-max gap-2 lg:w-auto lg:flex-col lg:gap-1"
                         @keydown.down.prevent="$el.querySelector('[aria-selected=true]').nextElementSibling?.focus(); $el.querySelector('[aria-selected=true]').nextElementSibling?.click()"
                         @keydown.up.prevent="$el.querySelector('[aria-selected=true]').previousElementSibling?.focus(); $el.querySelector('[aria-selected=true]').previousElementSibling?.click()"
                         @keydown.right.prevent="$el.querySelector('[aria-selected=true]').nextElementSibling?.focus(); $el.querySelector('[aria-selected=true]').nextElementSibling?.click()"
                         @keydown.left.prevent="$el.querySelector('[aria-selected=true]').previousElementSibling?.focus(); $el.querySelector('[aria-selected=true]').previousElementSibling?.click()">
                        @foreach($tourTypes as $t)
                            <a id="wtab-{{ $t->slug }}" role="tab" href="{{ route('tours.show', $t->slug) }}" aria-controls="wpanel-{{ $t->slug }}"
                               :aria-selected="way === '{{ $t->slug }}'" :tabindex="way === '{{ $t->slug }}' ? 0 : -1"
                               @click.prevent="way = '{{ $t->slug }}'"
                               class="block shrink-0 whitespace-nowrap rounded-full border-b-4 px-5 py-2 font-display text-xl leading-tight transition-colors lg:whitespace-normal lg:rounded-none lg:border-b-0 lg:border-l-4 lg:px-7 lg:py-5 lg:text-[1.75rem]"
                               :class="way === '{{ $t->slug }}' ? 'border-amber bg-sky-soft text-ink' : 'border-transparent text-ink/80 hover:bg-ink/5 hover:text-ink'">{{ $t->name }}</a>
                        @endforeach
                    </div>
                </div>

                {{-- Panel for the active tab: image, short description, link to that tour type's page --}}
                <div>
                    @foreach($tourTypes as $t)
                        <div id="wpanel-{{ $t->slug }}" role="tabpanel" aria-labelledby="wtab-{{ $t->slug }}" x-show="way === '{{ $t->slug }}'" @if(! $loop->first) style="display:none" @endif x-transition.opacity.duration.300ms>
                            <div class="aspect-[4/3] overflow-hidden rounded-[1.75rem] bg-sky-soft shadow-card lg:aspect-[16/11]">
                                @if($t->image)
                                    <x-photo :base="$t->image" :alt="$t->image_alt ?: $t->name" sizes="(min-width:1024px) 55vw, 92vw" fetchpriority="low" class="size-full" :ratio="[1600, 1100]" />
                                @endif
                            </div>
                            <p class="mt-6 max-w-xl text-lg leading-relaxed text-slate">{{ $t->summary }}</p>
                            <a href="{{ route('tours.show', $t->slug) }}" class="mt-4 inline-flex min-h-11 items-center gap-2 font-semibold underline decoration-amber decoration-2 underline-offset-4 hover:text-slate">
                                View tours<span class="sr-only"> for {{ $t->name }}</span> @icon('arrow-right', 'size-4')
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
