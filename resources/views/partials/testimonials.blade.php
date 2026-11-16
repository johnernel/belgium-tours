{{-- Testimonials carousel: slides move sideways, with arrows, swipe, arrow keys, round photo selectors and gentle auto-play
     (paused on hover/focus and with reduced motion). Logic: Alpine component `quoteCarousel`. Include with: testimonials, c. --}}
@if($testimonials->isNotEmpty())
    <section class="bg-ivory py-16 text-ink sm:py-24" aria-labelledby="t-title" x-data="quoteCarousel({{ $testimonials->count() }})">
        <div class="container-page text-center">
            <h2 id="t-title" class="text-heading">@heading($c('testimonials_heading') ?: 'What travellers *say*')</h2>

            <div class="mx-auto mt-10 max-w-5xl" role="region" aria-roledescription="carousel" aria-label="Traveller reviews"
                 @keydown.left.prevent="prev()" @keydown.right.prevent="next()"
                 @mouseenter="hold = true" @mouseleave="hold = false" @focusin="hold = true" @focusout="hold = false">
                <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-amber/25 font-serif text-6xl leading-none text-amber-ink" aria-hidden="true"><span class="translate-y-2.5">&ldquo;</span></span>

                <div class="mt-8 overflow-hidden" x-ref="viewport" @touchstart.passive="swipeStart($event)" @touchend.passive="swipeEnd($event)">
                    <div class="flex transition-transform duration-500 ease-out motion-reduce:transition-none" :style="'transform: translateX(-' + (i * 100) + '%)'">
                        @foreach($testimonials as $t)
                            <figure class="w-full shrink-0 px-2 sm:px-6" role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $loop->count }}" :aria-hidden="i !== {{ $loop->index }}" :inert="i !== {{ $loop->index }}">
                                <blockquote class="font-serif text-2xl font-light leading-snug text-ink sm:text-3xl lg:text-[2.5rem] lg:leading-tight">{{ $t->quote }}</blockquote>
                                <figcaption class="mt-8">
                                    <p class="font-sans text-sm font-semibold text-ink">{{ $t->name }}</p>
                                    @if($t->location)<p class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate">{{ $t->location }}</p>@endif
                                </figcaption>
                            </figure>
                        @endforeach
                    </div>
                </div>

                @if($testimonials->count() > 1)
                    <div class="mt-8 flex items-center justify-center gap-3 sm:gap-5">
                        <button type="button" @click="prev()" aria-label="Previous review"
                                class="flex size-11 shrink-0 items-center justify-center rounded-full border-2 border-ink/20 text-ink transition hover:border-ink hover:bg-ink hover:text-cream">
                            @icon('arrow-right', 'size-5 rotate-180')
                        </button>

                        <div class="flex justify-center gap-2 sm:gap-3" role="group" aria-label="Choose a review">
                            @foreach($testimonials as $t)
                                <button type="button" @click="go({{ $loop->index }})" :aria-pressed="i === {{ $loop->index }}"
                                        aria-label="Show review {{ $loop->iteration }}{{ $t->is_placeholder ? '' : ' from '.$t->name }}"
                                        class="relative size-12 overflow-hidden rounded-full ring-offset-2 ring-offset-ivory transition sm:size-16"
                                        :class="i === {{ $loop->index }} ? 'ring-2 ring-amber' : 'opacity-60 hover:opacity-100'">
                                    @if($t->photo)
                                        <x-photo :base="$t->photo" :alt="$t->photo_alt ?: ''" :widths="[480, 800]" sizes="64px" :ratio="[480, 480]" class="size-full" />
                                    @else
                                        {{-- Neutral person icon until a real, permitted photo is uploaded --}}
                                        <span class="flex size-full items-end justify-center {{ ['bg-sky', 'bg-cream-deep', 'bg-slate/40'][$loop->index % 3] }}" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" class="size-[85%] text-ink/70" fill="currentColor"><circle cx="12" cy="8.5" r="4" /><path d="M3.5 24c0-5 3.8-8 8.5-8s8.5 3 8.5 8z" /></svg>
                                        </span>
                                    @endif
                                </button>
                            @endforeach
                        </div>

                        <button type="button" @click="next()" aria-label="Next review"
                                class="flex size-11 shrink-0 items-center justify-center rounded-full border-2 border-ink/20 text-ink transition hover:border-ink hover:bg-ink hover:text-cream">
                            @icon('arrow-right', 'size-5')
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
