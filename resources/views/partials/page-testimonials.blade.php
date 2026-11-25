{{-- Testimonials band: video + cards (home and Packages page): shared partial, edit it here. Needs $testimonials; the heading copy comes from $c('testimonials_*') (the home page copy; $home on pages that have no $c). --}}
@php $c = $c ?? (isset($home) ? fn ($k) => $home->block($k) : fn () => null); @endphp
{{-- 8. TESTIMONIALS (layout copied from the client's reference): a full-width video block with a play button and a fade into the dark section, the centred label and heading,
     then the testimonials as a carousel (3 cards per view on desktop, 2 tablet, 1 mobile; arrows, dots, swipe, no autoplay). No star ratings: there are none to back them. --}}
@if($testimonials->isNotEmpty())
<section x-ignore data-lazy-alpine class="cv-auto relative isolate overflow-hidden bg-sky text-ink" aria-labelledby="ht-quotes">
    {{-- Video block: starts by itself (muted, looping) when the section scrolls into view and pauses when it leaves; the button pauses or resumes it. Reduced-motion and data-saver visitors get the poster and the play button instead. --}}
    <div class="relative h-[17rem] overflow-hidden sm:h-[26rem] lg:h-[32rem]" x-data="{ playing: false, userPaused: false }"
         x-init="const v = $refs.v; const calm = matchMedia('(prefers-reduced-motion: reduce)').matches || (navigator.connection && navigator.connection.saveData);
                 if (! calm && 'IntersectionObserver' in window) {
                     new IntersectionObserver(([e]) => {
                         if (e.isIntersecting) { if (! userPaused) { v.play().then(() => playing = true).catch(() => {}); } }
                         else { v.pause(); playing = false; }
                     }, { threshold: 0.35 }).observe($el);
                 }">
        <video x-ref="v" class="absolute inset-0 size-full object-cover" muted loop playsinline preload="none" tabindex="-1" aria-label="Belgium travel footage"
               :poster="'{{ asset('media/ways-poster.webp').'?v='.filemtime(public_path('media/ways-poster.webp')) }}'">
            <source src="{{ asset('media/ways-loop.webm').'?v='.filemtime(public_path('media/ways-loop.webm')) }}" type="video/webm">
            <source src="{{ asset('media/ways-loop.mp4').'?v='.filemtime(public_path('media/ways-loop.mp4')) }}" type="video/mp4">
        </video>
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-sky via-ink/10 to-ink/25"></div>
        <button type="button" @click="playing ? ($refs.v.pause(), playing = false, userPaused = true) : ($refs.v.play(), playing = true, userPaused = false)" aria-label="Play video" :aria-label="playing ? 'Pause video' : 'Play video'" :aria-pressed="playing"
                class="absolute left-1/2 top-[42%] grid size-20 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-ink shadow-lift transition duration-300 hover:scale-105 hover:bg-white focus-visible:opacity-100 sm:size-24"
                :class="playing ? 'opacity-0 hover:opacity-100' : 'opacity-100'">
            <svg x-show="!playing" viewBox="0 0 24 24" class="ml-1 size-9" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13a1 1 0 0 0 1.5.86l10.5-6.5a1 1 0 0 0 0-1.72L9.5 4.64A1 1 0 0 0 8 5.5Z"/></svg>
            <svg x-show="playing" x-cloak viewBox="0 0 24 24" class="size-9" fill="currentColor" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
        </button>
    </div>

    {{-- Decorative waffle hearts (client image, cream hearts on white): multiplied into the sage background so the white drops out, low opacity, edges feathered with a radial mask so it reads as part of the background --}}
    <div x-data :style="{ backgroundImage: 'url({{ asset('images/decor/waffle-hearts.webp') }})' }" class="pointer-events-none absolute inset-x-0 bottom-0 top-[14rem] bg-cover bg-center mix-blend-multiply opacity-80 [mask-image:radial-gradient(ellipse_at_center,#000_60%,transparent_90%)] sm:top-[26rem]" aria-hidden="true"></div>
    <div class="container-wide relative pb-16 sm:pb-24">
        <div class="-mt-10 text-center sm:-mt-14">
            @include('partials.ht-eyebrow', ['label' => $c('testimonials_eyebrow') ?: 'Testimonials', 'center' => true])
            <h2 id="ht-quotes" class="mt-5 font-serif text-heading">@heading($c('testimonials_heading') ?: 'What Our Guests *Say*')</h2>
        </div>

        <div class="mt-12" x-data="pageCarousel()" role="region" aria-roledescription="carousel" aria-label="Traveler testimonials">
            <div x-ref="track" @scroll.passive="update()" @keydown.arrow-left.prevent="prev()" @keydown.arrow-right.prevent="next()" tabindex="0" aria-label="Testimonials, swipe or use the arrows"
                 class="relative flex snap-x snap-mandatory items-stretch gap-3 overflow-x-auto scroll-smooth pb-2 [scrollbar-width:none] motion-reduce:scroll-auto max-sm:after:w-5 max-sm:after:shrink-0 max-sm:after:content-[''] sm:gap-6 [&::-webkit-scrollbar]:hidden">
                @foreach($testimonials as $t)
                    <figure class="flex w-[calc(100%-2rem)] shrink-0 snap-start flex-col rounded-md bg-white p-7 ring-1 ring-ink/10 sm:w-[calc((100%-1.5rem)/2)] lg:w-[calc((100%-3rem)/3)]" role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $loop->count }}">
                        <div class="flex items-start justify-between gap-4">
                            <svg viewBox="0 0 24 24" class="size-10 text-amber" fill="currentColor" aria-hidden="true"><path d="M7 7h4v4c0 3-1.6 5-4.5 5.5v-2C8 14 8.6 13 8.6 12H7zm8 0h4v4c0 3-1.6 5-4.5 5.5v-2C16 14 16.6 13 16.6 12H15z"/></svg>
                            @if($t->photo)
                                <span class="relative size-12 shrink-0 overflow-hidden rounded-full"><x-photo :base="$t->photo" :alt="$t->photo_alt ?: ''" :widths="[480, 800]" sizes="48px" :ratio="[480, 480]" class="size-full" /></span>
                            @else
                                <span class="grid size-12 shrink-0 place-items-center rounded-full bg-sky text-slate" aria-hidden="true"><svg viewBox="0 0 24 24" class="size-7" fill="currentColor"><circle cx="12" cy="8.5" r="4"/><path d="M3.5 24c0-5 3.8-8 8.5-8s8.5 3 8.5 8z"/></svg></span>
                            @endif
                        </div>
                        <blockquote class="mt-5 flex-1 text-[0.98rem] leading-relaxed text-ink">{{ $t->quote }}</blockquote>
                        <figcaption class="mt-6 border-t border-ink/10 pt-4"><p class="font-semibold">{{ $t->name }}</p>@if($t->location)<p class="mt-0.5 text-xs uppercase tracking-[0.16em] text-slate">{{ $t->location }}</p>@endif</figcaption>
                    </figure>
                @endforeach
            </div>

            <div x-show="pages > 1" x-cloak class="mt-8 flex items-center justify-center gap-4">
                <button type="button" @click="prev()" :disabled="page === 0" aria-label="Previous testimonials" class="grid size-11 place-items-center rounded-full border-2 border-ink/25 text-ink transition hover:border-amber-text hover:text-amber-text disabled:pointer-events-none disabled:opacity-30">@icon('arrow-right', 'size-5 rotate-180')</button>
                <div class="flex items-center gap-0" role="group" aria-label="Choose a page">
                    <template x-for="p in pages" :key="p">
                        <button type="button" @click="go(p - 1)" :aria-label="'Page ' + p" :aria-current="page === p - 1 ? 'true' : null" class="group grid h-6 place-items-center" :class="page === p - 1 ? 'w-14' : 'w-6'"><span class="block h-2 rounded-full transition-all duration-300" :class="page === p - 1 ? 'w-12 bg-amber' : 'w-2.5 bg-ink/20 group-hover:bg-ink/40'"></span></button>
                    </template>
                </div>
                <button type="button" @click="next()" :disabled="page >= pages - 1" aria-label="Next testimonials" class="grid size-11 place-items-center rounded-full border-2 border-ink/25 text-ink transition hover:border-amber-text hover:text-amber-text disabled:pointer-events-none disabled:opacity-30">@icon('arrow-right', 'size-5')</button>
            </div>
        </div>
    </div>
</section>
@endif
