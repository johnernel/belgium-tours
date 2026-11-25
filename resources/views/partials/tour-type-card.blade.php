{{-- Tour type photo card (home "Three Ways to Travel" and the "Other ways to see Belgium" band on the tour type pages): full-bleed photo under a dark
     bottom gradient, serif title, tagline, small uppercase "Best for" line and an outlined square "Get Quote" button, all bottom-left. Include with: t (TourType).
     Optional: cls (extra classes for the article, e.g. the phone carousel width). The look lives here only. --}}
@php
    $bestFor = ['self-drive' => 'Couples · Families · Independent travelers', 'chauffeur-driven' => 'Couples · Solo travelers · Luxury seekers', 'coach-tours' => 'Solo travelers · Couples · Groups'];
@endphp
<article class="group relative isolate flex min-h-[26rem] flex-col justify-end overflow-hidden bg-ink p-6 text-white sm:min-h-[30rem] lg:min-h-[32rem] lg:p-8 {{ $cls ?? '' }}">
    @if($t->image)<x-photo :base="$t->image" alt="" :widths="[480, 800, 1280, 1600]" sizes="(min-width:768px) 34vw, 100vw" fetchpriority="low" :ratio="[1600, 1067]" class="absolute inset-0 -z-20 size-full transition-transform duration-700 group-hover:scale-105" />@endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/90 via-ink/30 to-transparent"></div>
    <h3 class="font-display text-3xl font-semibold leading-tight text-white lg:text-[2rem]"><a href="{{ route('tours.show', $t->slug) }}" class="hover:text-amber">{{ $t->name }}</a></h3>
    @if($t->menu_text)<p class="mt-1 text-[0.95rem] text-white">{{ $t->menu_text }}</p>@endif
    @if(isset($bestFor[$t->slug]))<p class="mt-5 text-xs font-medium uppercase tracking-[0.08em] text-white/85"><span class="text-white/70">Best for</span> · {{ $bestFor[$t->slug] }}</p>@endif
    <div class="mt-4">
        <button type="button" @click="$store.modal.open({ tourType: {{ \Illuminate\Support\Js::from($t->name) }} })" class="inline-flex min-h-11 items-center border border-white px-6 text-sm font-semibold uppercase tracking-wide text-white transition hover:bg-white hover:text-ink">Get Quote</button>
    </div>
</article>