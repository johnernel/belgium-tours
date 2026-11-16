{{-- Include with: experiences, eyebrow, heading, cta --}}
@php $first = $experiences->first()?->slug; @endphp
<section class="py-16 sm:py-24" aria-labelledby="dest-title" x-data="{ dest: @js($first) }">
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
        <div class="mt-10 grid gap-8 lg:grid-cols-[0.85fr_1.15fr] lg:gap-14">
            {{-- Tabs: a vertical list on desktop, a scrolling row on mobile --}}
            <div class="-mx-4 overflow-x-auto px-4 [scrollbar-width:none] sm:-mx-6 sm:px-6 lg:mx-0 lg:overflow-visible lg:px-0 [&::-webkit-scrollbar]:hidden">
                <div role="tablist" aria-label="Top destinations" aria-orientation="vertical" class="flex w-max gap-2 lg:w-auto lg:flex-col lg:gap-1"
                     @keydown.down.prevent="$el.querySelector('[aria-selected=true]').nextElementSibling?.focus(); $el.querySelector('[aria-selected=true]').nextElementSibling?.click()"
                     @keydown.up.prevent="$el.querySelector('[aria-selected=true]').previousElementSibling?.focus(); $el.querySelector('[aria-selected=true]').previousElementSibling?.click()"
                     @keydown.right.prevent="$el.querySelector('[aria-selected=true]').nextElementSibling?.focus(); $el.querySelector('[aria-selected=true]').nextElementSibling?.click()"
                     @keydown.left.prevent="$el.querySelector('[aria-selected=true]').previousElementSibling?.focus(); $el.querySelector('[aria-selected=true]').previousElementSibling?.click()">
                    @foreach($experiences as $e)
                        <a id="dtab-{{ $e->slug }}" role="tab" href="{{ route('packages.index', ['experience' => $e->slug]) }}" aria-controls="dpanel-{{ $e->slug }}"
                           :aria-selected="dest === '{{ $e->slug }}'" :tabindex="dest === '{{ $e->slug }}' ? 0 : -1"
                           @click.prevent="dest = '{{ $e->slug }}'"
                           class="block shrink-0 whitespace-nowrap rounded-full border-b-4 px-5 py-2 font-display text-xl leading-tight transition-colors lg:whitespace-normal lg:rounded-none lg:border-b-0 lg:border-l-4 lg:px-7 lg:py-5 lg:text-[1.75rem]"
                           :class="dest === '{{ $e->slug }}' ? 'border-amber bg-sky-soft text-ink' : 'border-transparent text-ink/80 hover:bg-ink/5 hover:text-ink'">{{ $e->name }}</a>
                    @endforeach
                </div>
            </div>

            {{-- Panel for the active tab --}}
            <div>
                @foreach($experiences as $e)
                    <div id="dpanel-{{ $e->slug }}" role="tabpanel" aria-labelledby="dtab-{{ $e->slug }}" x-show="dest === '{{ $e->slug }}'" @if(! $loop->first) style="display:none" @endif x-transition.opacity.duration.300ms>
                        <div class="aspect-[4/3] overflow-hidden rounded-[1.75rem] bg-sky-soft shadow-card lg:aspect-[16/11]">
                            @if($e->image)
                                <x-photo :base="$e->image" :alt="$e->image_alt ?: $e->name" sizes="(min-width:1024px) 55vw, 92vw" class="size-full" :ratio="[1600, 1100]" />
                            @endif
                        </div>
                        <p class="mt-6 max-w-xl text-lg leading-relaxed text-slate">{{ $e->description }}</p>
                        <a href="{{ route('packages.index', ['experience' => $e->slug]) }}" class="mt-4 inline-flex min-h-11 items-center gap-2 font-semibold underline decoration-amber decoration-2 underline-offset-4 hover:text-slate">
                            View tours<span class="sr-only"> for {{ $e->name }}</span> @icon('arrow-right', 'size-4')
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>
