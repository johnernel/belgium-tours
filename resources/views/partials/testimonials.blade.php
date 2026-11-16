{{-- Testimonials: one big quote at a time with a row of round photo selectors. Include with: testimonials, c. --}}
@if($testimonials->isNotEmpty())
    <section class="bg-ivory py-16 text-ink sm:py-24" aria-labelledby="t-title" x-data="{ i: 0 }">
        <div class="container-page text-center">
            <h2 id="t-title" class="text-heading">@heading($c('testimonials_heading') ?: 'What travellers *say*')</h2>

            <div class="mx-auto mt-10 max-w-5xl">
                <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-amber/25 font-serif text-6xl leading-none text-amber-ink" aria-hidden="true"><span class="translate-y-2.5">&ldquo;</span></span>

                <div class="mt-8" aria-live="polite">
                    @foreach($testimonials as $t)
                        <figure x-show="i === {{ $loop->index }}" @if(! $loop->first) style="display:none" @endif x-transition.opacity.duration.300ms>
                            <blockquote class="font-serif text-2xl font-light leading-snug text-ink sm:text-3xl lg:text-[2.5rem] lg:leading-tight">{{ $t->quote }}</blockquote>
                            <figcaption class="mt-8">
                                <p class="font-sans text-sm font-semibold text-ink">{{ $t->name }}</p>
                                @if($t->location)<p class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate">{{ $t->location }}</p>@endif
                            </figcaption>
                        </figure>
                    @endforeach
                </div>

                @if($testimonials->count() > 1)
                    <div class="mt-8 flex justify-center gap-3" role="group" aria-label="Choose a review">
                        @foreach($testimonials as $t)
                            <button type="button" @click="i = {{ $loop->index }}" :aria-pressed="i === {{ $loop->index }}"
                                    aria-label="Show review {{ $loop->iteration }}{{ $t->is_placeholder ? '' : ' from '.$t->name }}"
                                    class="relative size-14 overflow-hidden rounded-full ring-offset-2 ring-offset-ivory transition sm:size-16"
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
                @endif
            </div>
        </div>
    </section>
@endif
