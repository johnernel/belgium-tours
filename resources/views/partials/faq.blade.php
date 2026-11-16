{{-- Home FAQ: photo + "still have questions" card on the left, accordion on the right (native <details>, works without JS). Uses $c, $faqs, $site. --}}
<section class="bg-ivory py-16 sm:py-24" aria-labelledby="faq-title">
    <div class="container-page grid gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:gap-16">
        <div class="order-2 lg:order-1">
            <div class="relative aspect-[4/3] overflow-hidden rounded-3xl shadow-card lg:aspect-[9/8]">
                <x-photo base="images/photos/bruges-market" alt="The Belfry rising above the Markt square in Bruges at dusk" :widths="[480, 800, 1280]" sizes="(min-width:1024px) 40vw, 92vw" fetchpriority="low" :ratio="[3840, 2560]" class="absolute inset-0 size-full" />
            </div>
            <div class="on-dark mt-4 rounded-3xl bg-dusk p-6 text-cream sm:p-7">
                <h3 class="font-serif text-2xl font-normal text-cream">{{ $c('faq_card_title') ?: 'Still have questions?' }}</h3>
                <p class="mt-2 max-w-sm text-[0.95rem] leading-relaxed text-cream/90">{{ $c('faq_card_text') ?: 'We are here to help with any queries. Reach out and get clear answers directly from our team.' }}</p>
                <div class="mt-5 flex flex-wrap gap-3">
                    @if($site->phone)
                        <x-button variant="light" size="sm" :href="'tel:'.$site->phone_href">@icon('phone', 'size-4') {{ $c('faq_call') ?: 'Call us' }}</x-button>
                    @endif
                    <x-button variant="cta" size="sm" icon="arrow-right" @click="$store.modal.open()">{{ $c('faq_cta') ?: 'Get Quote Now' }}</x-button>
                </div>
            </div>
        </div>

        <div class="order-1 lg:order-2">
            <span class="inline-block rounded-full bg-amber/25 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-amber-ink">{{ $c('faq_eyebrow') ?: 'FAQ' }}</span>
            <h2 id="faq-title" class="mt-5 text-heading">@heading($c('faq_heading') ?: 'Questions, *answered*')</h2>

            <div class="mt-8 border-t border-ink/10">
                @foreach($faqs as $f)
                    <details name="home-faq" @if($loop->first) open @endif class="group border-b border-ink/10 transition-colors open:rounded-2xl open:border-transparent open:bg-amber/20">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 rounded-2xl px-4 py-5 font-serif text-lg font-normal leading-snug text-ink sm:px-6 sm:text-xl [&::-webkit-details-marker]:hidden">
                            <span>{{ $f->question }}</span>
                            @icon('chevron-down', 'size-5 shrink-0 text-amber-ink transition-transform group-open:rotate-180')
                        </summary>
                        <div class="px-4 pb-6 text-[0.95rem] leading-relaxed text-ink/85 sm:px-6">{{ $f->answer }}</div>
                    </details>
                @endforeach
            </div>
        </div>
    </div>
</section>
