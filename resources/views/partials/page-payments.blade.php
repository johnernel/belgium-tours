{{-- Simple, Secure Payments band (home and Packages page): shared partial, edit it here. --}}
{{-- 9b. FIND YOUR STYLE: simple, secure payments (layout copied from the client's reference, wording rewritten for Belgium): centred label, heading and two-line intro,
     four equal square cards (round dark icon, two-line title, centred text), one centred button underneath. The payment wording is placeholder copy until the client confirms how payments work. --}}
@php
    $pay = [
        ['smartphone', 'images/photos/countryside', 'No More Payment Headaches', 'Let each traveler pay securely online, with no more chasing or managing group payments.'],
        ['shield', 'images/photos/liege-guillemins', 'Secure & Convenient', 'Fast, safe and hassle-free transactions so you can book with complete confidence.'],
        ['bookmark', 'images/photos/bruges-belfry', 'Instant Booking Confirmation', 'Once your group is finalised, booking is confirmed immediately, with no back and forth.'],
        ['banknote', 'images/photos/bruges-market', 'Flexible Payment Options', 'Multiple secure payment options available to suit every traveler in your whole group.'],
    ];
@endphp
<section x-ignore data-lazy-alpine class="cv-auto bg-white py-16 sm:py-24" aria-labelledby="ht-pay">
    <div class="container-wide">
        <div class="mx-auto max-w-4xl text-center">
            @include('partials.ht-eyebrow', ['label' => 'Find your style', 'center' => true])
            <h2 id="ht-pay" class="mt-5 font-serif text-heading">@heading('Simple, Secure Payments, No *Worries Ever*')</h2>
            <p class="mx-auto mt-5 max-w-3xl text-lg text-ink">Whether you are traveling with friends, family or colleagues, our flexible group payment options make booking your Belgium trip simple, straightforward and completely hassle-free for everyone involved.</p>
        </div>
        {{-- Phones (below sm): a swipeable scroll-snap carousel, one card at a time with the next card peeking 20px in from the right, and pill dots like the package carousels; from sm up the 2 / 4 column grid is unchanged --}}
        <div class="mt-12 min-w-0" x-data="pageCarousel()">
        <div x-ref="track" @scroll.passive="update()" tabindex="0" aria-label="Payment options, swipe to see more" class="relative grid gap-6 sm:grid-cols-2 xl:grid-cols-4 xl:gap-8 max-sm:flex max-sm:snap-x max-sm:snap-mandatory max-sm:gap-3 max-sm:overflow-x-auto max-sm:scroll-smooth max-sm:pb-6 max-sm:[scrollbar-width:none] max-sm:after:w-5 max-sm:after:shrink-0 max-sm:after:content-['] max-sm:motion-reduce:scroll-auto max-sm:[&::-webkit-scrollbar]:hidden">
            @foreach($pay as [$ic, $img, $title, $text])
                {{-- Card copied from the client's reference: rounded corners and a big soft shadow, a white round icon with an amber ring, serif title, centred text, and a faded photo that fades in from the middle of the card down. The white wash over the photo keeps the text readable. --}}
                <article class="relative isolate flex min-h-[22rem] flex-col items-center overflow-hidden rounded-lg bg-white px-5 pb-10 pt-9 text-center shadow-lift max-sm:w-[calc(100%-2rem)] max-sm:shrink-0 max-sm:snap-start xl:px-4">
                    <div class="absolute inset-x-0 bottom-0 -z-20 h-[80%] opacity-35 [mask-image:linear-gradient(to_bottom,transparent,black_45%)]" aria-hidden="true">
                        <x-photo :base="$img" alt="" :widths="[480, 800]" sizes="(min-width:1024px) 25vw, 90vw" fetchpriority="low" :ratio="[1600, 1067]" class="size-full" />
                    </div>
                    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-white via-white/75 to-white/80" aria-hidden="true"></div>
                    <span class="grid size-20 place-items-center rounded-full border-2 border-amber bg-white text-slate shadow-card [&_svg]:![stroke-width:1.25]">@icon($ic, 'size-9')</span>
                    <h3 class="mt-7 max-w-[12rem] font-display text-[1.4rem] font-semibold leading-tight text-ink">{{ $title }}</h3>
                    <p class="mt-5 line-clamp-3 text-[0.97rem] font-medium leading-relaxed text-ink xl:text-[0.92rem]">{{ $text }}</p>
                </article>
            @endforeach
        </div>
        <div x-show="pages > 1" x-cloak class="mt-1 flex items-center justify-center gap-0 sm:hidden" role="group" aria-label="Choose a payment option">
            <template x-for="p in pages" :key="p">
                <button type="button" @click="go(p - 1)" :aria-label="'Option ' + p" :aria-current="page === p - 1 ? 'true' : null" class="group grid h-6 place-items-center" :class="page === p - 1 ? 'w-14' : 'w-6'"><span class="block h-2 rounded-full transition-all duration-300" :class="page === p - 1 ? 'w-12 bg-amber' : 'w-2.5 bg-ink/20 group-hover:bg-ink/40'"></span></button>
            </template>
        </div>
        </div>
        <div class="mt-12 text-center"><x-button square variant="cta" size="lg" @click="$store.modal.open()">Get Your Free Quote</x-button></div>
    </div>
</section>
