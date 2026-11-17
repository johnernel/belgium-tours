{{-- "Tailor-made in Belgium": two photos, heading, three points and the CTA. Shared by the home page and About Us. Uses the $c content closure (Admin > Pages > Home). --}}
@php
    // Two-colour heading: the last word (or the *starred* part) is the italic accent.
    $why = trim($c('why_heading') ?: 'Every journey, personally crafted.');
    $whyPhotos = [
        ['images/photos/flower-carpet', 'Flower carpet laid out in front of the Brussels Grand Place', [1600, 1023]],
        ['images/photos/gravensteen', 'The medieval Gravensteen castle in Ghent at sunset', [1600, 1044]],
    ];
@endphp
<section class="relative py-16 sm:py-24" aria-labelledby="why-title">
    <div class="container-page grid gap-10 lg:grid-cols-2 lg:items-stretch lg:gap-16">
        {{-- Photos fill the full height of the text column, so both sides stay balanced --}}
        <div class="grid h-80 grid-cols-2 gap-3 sm:h-96 sm:gap-4 lg:h-auto lg:min-h-[30rem]">
            @foreach($whyPhotos as [$base, $alt, $ratio])
                <div class="relative overflow-hidden rounded-3xl shadow-card">
                    <x-photo :base="$base" :alt="$alt" :widths="[480, 800, 1280, 1600, 2400]" sizes="(min-width:1024px) 1000px, (min-width:640px) 640px, 400px" fetchpriority="low" :ratio="$ratio" class="absolute inset-0 size-full" />
                </div>
            @endforeach
        </div>

        <div class="flex flex-col justify-center lg:max-w-xl">
            <p class="flex items-center gap-4 text-xs font-semibold uppercase tracking-[0.22em] text-slate">
                <span class="h-px w-10 bg-amber" aria-hidden="true"></span>{{ $c('why_eyebrow') ?: 'Tailor-made in Belgium' }}
            </p>
            <h2 id="why-title" class="mt-5 font-serif text-heading">@heading($why)</h2>
            <p class="mt-6 text-lg leading-relaxed text-slate">{{ $c('why_sub') }}</p>

            <ul class="mt-8 divide-y divide-ink/10 border-t border-ink/10">
                @foreach([1, 2, 3] as $n)
                    <li class="grid grid-cols-[2.75rem_1fr] items-baseline gap-x-2 py-4">
                        <span class="font-serif text-lg italic text-amber-ink" aria-hidden="true">{{ sprintf('%02d', $n) }}</span>
                        <div>
                            <h3 class="font-serif text-xl font-normal leading-snug">{{ $c("why_{$n}_title") }}</h3>
                            <p class="mt-1 text-[0.95rem] text-slate">{{ $c("why_{$n}_text") }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="mt-8">
                <x-button variant="cta" size="lg" icon="arrow-right" @click="$store.modal.open()">{{ $c('why_cta') ?: 'Plan My Journey' }}</x-button>
            </div>
        </div>
    </div>
</section>
