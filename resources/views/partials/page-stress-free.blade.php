{{-- Stress-free travel band (home and Packages page): shared partial, edit it here. --}}
{{-- 7. STRESS-FREE TRAVEL (layout copied from the client's reference, wording rewritten for Belgium): centred label, heading and one-line intro, then three equal square-cornered cards, each with a round icon, a bold title and a centred description --}}
@php
    $calm = [
        ['detail', 'Every Detail. Already Handled.', 'Our Belgium specialists plan every detail: hand-picked stays, ideal pacing and local stops, so nothing is left to chance.'],
        ['local', 'Local access. Real moments.', 'Our local connections open doors beyond the tourist routes: insider tips, quiet corners and the true heart of Belgium, hassle-free.'],
        ['travel', 'Travel Your Way', 'Take the wheel on a self-drive adventure, or relax with a private chauffeur or coach tour. Every journey is seamless.'],
    ];
@endphp
<section x-ignore data-lazy-alpine class="on-dark relative isolate overflow-hidden bg-ink py-16 text-white sm:py-24" aria-labelledby="ht-steps">
    {{-- Photo background under a dark wash (layout copied from the client's reference): label, heading and intro centred, three frosted glass cards, a hairline, then one button --}}
    {{-- Fixed background (background-attachment: fixed): stays put while the page scrolls. No cv-auto on this section, because content-visibility contains fixed layers. iOS Safari ignores fixed and scrolls it normally. --}}
    <div x-data :style="{ backgroundImage: 'url({{ asset('images/photos/ghent-graslei-1280.webp') }})' }" class="absolute inset-0 -z-20 bg-ink bg-cover bg-fixed bg-center" aria-hidden="true"></div>
    <div class="absolute inset-0 -z-10 bg-ink/55" aria-hidden="true"></div>
    <div class="container-wide">
        <div class="mx-auto max-w-4xl text-center">
            @include('partials.ht-eyebrow', ['label' => 'Stress-free travel', 'center' => true, 'dark' => true])
            <h2 id="ht-steps" class="mt-5 font-serif text-heading !text-white">@heading('Discover Belgium the *Stress-Free* Way')</h2>
            <p class="mx-auto mt-5 max-w-3xl text-lg text-white/90">We take care of every detail so you can simply show up, enjoy the canals and cobbled squares, and let Belgium do the rest.</p>
        </div>
        {{-- Phones and small tablets (below md): a swipeable scroll-snap carousel, one card at a time with the next card peeking 20px in from the right, and pill dots; from md up the 3 equal columns --}}
        <div class="mt-12 min-w-0" x-data="pageCarousel()">
            <div x-ref="track" @scroll.passive="update()" tabindex="0" aria-label="Stress-free travel, swipe to see more"
                 class="relative grid gap-5 md:grid-cols-3 lg:gap-6 max-md:flex max-md:snap-x max-md:snap-mandatory max-md:gap-3 max-md:overflow-x-auto max-md:scroll-smooth max-md:[scrollbar-width:none] max-md:after:w-5 max-md:after:shrink-0 max-md:after:content-[''] max-md:motion-reduce:scroll-auto max-md:[&::-webkit-scrollbar]:hidden">
                @foreach($calm as [$ic, $title, $text])
                    <article class="flex flex-col items-center border border-white/25 bg-white/10 px-7 pb-10 pt-9 text-center backdrop-blur-md max-md:w-[calc(100%-2rem)] max-md:shrink-0 max-md:snap-start">
                        <span class="grid size-16 place-items-center rounded-full bg-mint text-teal-deep"><span class="qicon size-9" style="--qi: url({{ asset('images/calm-icons/'.$ic.'.svg') }})" aria-hidden="true"></span></span>
                        <h3 class="mt-6 font-display text-xl leading-snug text-white lg:text-[1.35rem]">{{ $title }}</h3>
                        <p class="mt-3 text-base leading-relaxed text-white/90">{{ $text }}</p>
                    </article>
                @endforeach
            </div>
            <div x-show="pages > 1" x-cloak class="mt-6 flex items-center justify-center gap-0 md:hidden" role="group" aria-label="Choose a card">
                <template x-for="p in pages" :key="p">
                    <button type="button" @click="go(p - 1)" :aria-label="'Card ' + p" :aria-current="page === p - 1 ? 'true' : null" class="group grid h-6 place-items-center" :class="page === p - 1 ? 'w-14' : 'w-6'"><span class="block h-2 rounded-full transition-all duration-300" :class="page === p - 1 ? 'w-12 bg-amber' : 'w-2.5 bg-white/30 group-hover:bg-white/60'"></span></button>
                </template>
            </div>
        </div>
        <div class="mt-12 border-t border-white/25 pt-10 text-center"><x-button square variant="cta" size="lg" @click="$store.modal.open()">Get a Free Quote</x-button></div>
    </div>
</section>
