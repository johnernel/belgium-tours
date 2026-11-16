{{-- Include with: c (content closure). Dusk background, cream text, amber accents, aqua only for small details. --}}
@php
    $features = collect([1, 2, 3, 4])->map(fn ($n) => ['icon' => ['map-pin', 'compass', 'bed', 'shield'][$n - 1], 'title' => $c("wcu_{$n}_title"), 'text' => $c("wcu_{$n}_text")])->filter(fn ($f) => $f['title']);
    // Numbers are only shown once the client has confirmed real figures (Admin > Pages > Home); nothing is invented.
    $stats = collect([1, 2, 3, 4])->map(fn ($n) => ['value' => $c("wcu_stat_{$n}_value"), 'label' => $c("wcu_stat_{$n}_label")])->filter(fn ($s) => $s['value'] && $s['label']);
    $badgeValue = $c('wcu_badge_value');
@endphp
<section class="on-dark bg-dusk py-16 text-cream sm:py-24" aria-labelledby="wcu-title">
    <div class="container-page">
        <div class="grid gap-10 lg:grid-cols-2 lg:items-center lg:gap-16">
            <div class="relative">
                <div class="relative h-[26rem] overflow-hidden rounded-3xl shadow-card sm:h-[32rem] lg:h-[36rem]">
                    <x-photo base="images/photos/bruges-belfry" alt="The Belfry tower rising above the rooftops of Bruges" :widths="[480, 800, 1280, 1600]" sizes="(min-width:1024px) 600px, 92vw" fetchpriority="low" :ratio="[3840, 2560]" class="absolute inset-0 size-full" />
                </div>
                @if($badgeValue)
                    <div class="absolute -bottom-6 right-4 flex size-28 flex-col items-center justify-center rounded-full bg-amber text-center text-ink shadow-card sm:right-8 sm:size-32">
                        <span class="font-serif text-3xl leading-none">{{ $badgeValue }}</span>
                        <span class="mt-1 max-w-[6rem] text-[0.65rem] font-semibold uppercase leading-tight tracking-widest">{{ $c('wcu_badge_label') }}</span>
                    </div>
                @endif
            </div>

            <div>
                <p class="flex items-center gap-4 text-xs font-semibold uppercase tracking-[0.22em] text-cream">
                    <span class="h-px w-10 bg-amber" aria-hidden="true"></span>{{ $c('wcu_eyebrow') ?: 'Why travel with us' }}
                </p>
                <h2 id="wcu-title" class="mt-5 font-serif text-heading text-cream">@heading($c('wcu_heading') ?: 'Quietly *exceptional* service.')</h2>
                <p class="mt-6 max-w-lg text-lg leading-relaxed text-cream/90">{{ $c('wcu_sub') }}</p>

                <ul class="mt-8 grid gap-x-8 gap-y-7 sm:grid-cols-2">
                    @foreach($features as $f)
                        <li class="flex gap-4">
                            <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-cream/10 text-amber ring-1 ring-sky/40">@icon($f['icon'], 'size-5')</span>
                            <div>
                                <h3 class="font-serif text-xl font-normal leading-snug text-cream">{{ $f['title'] }}</h3>
                                <p class="mt-1 text-[0.95rem] leading-relaxed text-cream/90">{{ $f['text'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        @if($stats->isNotEmpty())
            <div class="mt-16 grid overflow-hidden rounded-2xl bg-ink sm:mt-20 {{ ['', 'sm:grid-cols-1', 'sm:grid-cols-2', 'sm:grid-cols-3', 'sm:grid-cols-4'][min($stats->count(), 4)] }}">
                @foreach($stats as $s)
                    <div class="border-b border-cream/20 px-6 py-8 text-center last:border-b-0 sm:border-b-0 sm:border-r sm:last:border-r-0">
                        <p class="font-serif text-4xl text-amber sm:text-5xl">{{ $s['value'] }}</p>
                        <p class="mt-2 text-xs font-semibold uppercase tracking-[0.18em] text-cream/90">{{ $s['label'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
