{{-- How it works: intro + CTA on the left, three numbered steps along a curved line on the right (straight line on mobile). Uses $c. --}}
@php
    // Points on the arc (percent of the box). y is the position down the arc, x how far it bulges right.
    $points = [['19%', '14%'], ['43%', '50%'], ['19%', '86%']];
    // Where each step's text sits relative to its circle so the curve never runs through it: 1 above, 2 beside, 3 below.
    $place = [
        'lg:items-end lg:-translate-y-[calc(100%-1.75rem)]',
        'lg:items-start lg:-translate-y-7',
        'lg:items-start lg:-translate-y-7',
    ];
    $textPad = ['lg:pl-12', '', 'lg:pt-8'];
@endphp
<section class="py-16 sm:py-24" aria-labelledby="how-title">
    <div class="container-page grid gap-12 lg:grid-cols-[0.8fr_1.2fr] lg:items-center lg:gap-10">
        <div>
            <span class="inline-block rounded-full bg-sky-soft px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-ink">{{ $c('steps_eyebrow') ?: '3 easy steps' }}</span>
            <h2 id="how-title" class="mt-6 text-heading">@heading($c('steps_heading') ?: 'How it *works*')</h2>
            <p class="mt-6 max-w-md text-lg leading-relaxed text-slate">{{ $c('steps_sub') }}</p>
            <div class="mt-8">
                <x-button variant="cta" size="lg" icon="arrow-right" @click="$store.modal.open()">{{ $c('steps_cta') ?: 'Get my free quote' }}</x-button>
            </div>
        </div>

        <div class="relative lg:h-[34rem]">
            <svg class="pointer-events-none absolute inset-0 hidden size-full text-slate/50 lg:block" viewBox="0 0 100 100" preserveAspectRatio="none" fill="none" aria-hidden="true">
                <path d="M 8 0 Q 78 50 8 100" stroke="currentColor" stroke-width="1.5" vector-effect="non-scaling-stroke" />
            </svg>
            <ol class="relative space-y-10 lg:absolute lg:inset-0 before:absolute before:bottom-6 before:left-7 before:top-6 before:w-px before:bg-slate/40 lg:space-y-0 lg:before:hidden">
                @foreach([1, 2, 3] as $n)
                    <li class="relative flex items-start gap-5 lg:absolute lg:left-[var(--x)] lg:top-[var(--y)] lg:w-[calc(100%-var(--x)+1.75rem)] lg:-translate-x-7 {{ $place[$n - 1] }}"
                        style="--x: {{ $points[$n - 1][0] }}; --y: {{ $points[$n - 1][1] }}">
                        <span class="flex size-14 shrink-0 items-center justify-center rounded-full border-2 border-amber bg-paper font-serif text-lg text-ink shadow-card" aria-hidden="true">{{ sprintf('%02d', $n) }}</span>
                        <div class="pt-1 {{ $textPad[$n - 1] }}">
                            <h3 class="text-xl">{{ $c("step_{$n}_title") }}</h3>
                            <p class="mt-1.5 max-w-sm text-slate">{{ $c("step_{$n}_text") }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</section>
