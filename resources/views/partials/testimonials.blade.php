{{-- Testimonials: one big quote at a time with a row of round selectors. Include with: testimonials, c. --}}
@if($testimonials->isNotEmpty())
    @php
        $initials = function ($t, $n) {
            if ($t->is_placeholder) {
                return (string) $n; // placeholder names are not real people: number them instead
            }
            $words = preg_split('/\s+/', trim(preg_replace('/[^\p{L}\s]/u', ' ', (string) $t->name)), -1, PREG_SPLIT_NO_EMPTY);

            return mb_strtoupper(mb_substr($words[0] ?? '', 0, 1).mb_substr($words[1] ?? '', 0, 1));
        };
    @endphp
    <section class="bg-amber py-16 text-ink sm:py-24" aria-labelledby="t-title" x-data="{ i: 0 }">
        <div class="container-page text-center">
            <h2 id="t-title" class="text-heading">@heading($c('testimonials_heading') ?: 'What travellers *say*')</h2>

            <div class="mx-auto mt-10 max-w-3xl">
                <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-cream font-serif text-6xl leading-none text-ink" aria-hidden="true"><span class="translate-y-2.5">&ldquo;</span></span>

                <div class="mt-8" aria-live="polite">
                    @foreach($testimonials as $t)
                        <figure x-show="i === {{ $loop->index }}" @if(! $loop->first) style="display:none" @endif x-transition.opacity.duration.300ms>
                            <blockquote class="font-serif text-2xl font-light leading-snug text-ink sm:text-3xl lg:text-[2.5rem] lg:leading-tight">{{ $t->quote }}</blockquote>
                            <figcaption class="mt-8">
                                <p class="font-sans text-sm font-semibold text-ink">{{ $t->name }}</p>
                                @if($t->location)<p class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-ink/80">{{ $t->location }}</p>@endif
                            </figcaption>
                        </figure>
                    @endforeach
                </div>

                @if($testimonials->count() > 1)
                    <div class="mt-8 flex justify-center gap-3" role="group" aria-label="Choose a review">
                        @foreach($testimonials as $t)
                            <button type="button" @click="i = {{ $loop->index }}" :aria-pressed="i === {{ $loop->index }}" aria-label="Show review {{ $loop->iteration }}{{ $t->is_placeholder ? '' : ' from '.$t->name }}"
                                    class="flex size-12 items-center justify-center rounded-full font-serif text-lg transition sm:size-14"
                                    :class="i === {{ $loop->index }} ? 'bg-cream text-ink ring-2 ring-ink ring-offset-2 ring-offset-amber' : 'bg-cream/40 text-ink hover:bg-cream/70'">{{ $initials($t, $loop->iteration) }}</button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
