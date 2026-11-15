@php
    $navTypes = \App\Models\TourType::active()->get();
    $links = [
        ['Home', route('home'), request()->routeIs('home')],
        ['Packages', route('packages.index'), request()->routeIs('packages.*')],
        ['About', route('about'), request()->routeIs('about')],
        ['Contact', route('contact'), request()->routeIs('contact')],
    ];
    $pill = 'rounded-full px-4 py-2 text-[0.95rem] font-semibold transition-colors';
@endphp
{{-- Floating navigation: the bar itself has no background, so the hero photo shows through.
     Each piece (logo chip, link pill, quote button) carries its own surface so it stays readable on any page. --}}
<header class="sticky top-0 z-40 h-20" x-data="{ open: false, tours: false }" @keydown.escape.window="open = false; tours = false">
    <div class="container-page flex h-full items-center justify-between gap-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 rounded-full bg-paper py-1.5 pl-1.5 pr-4 shadow-card">
            @if($site->logo)
                <img src="{{ asset($site->logo) }}" alt="{{ $site->brand }}" class="h-9 w-auto" width="140" height="36">
            @else
                <span class="grid size-9 place-items-center rounded-full bg-ink text-amber" aria-hidden="true">@icon('compass', 'size-5')</span>
                <span class="font-display text-lg font-semibold leading-none tracking-tight">Go Belgium<span class="block text-[0.65rem] font-medium tracking-[0.22em] text-slate">TOURS</span><span class="sr-only"> home</span></span>
            @endif
        </a>

        <nav class="hidden items-center gap-1 rounded-full bg-ink/80 p-1.5 text-white shadow-lift ring-1 ring-white/10 backdrop-blur-md lg:flex" aria-label="Main">
            <a href="{{ $links[0][1] }}" class="{{ $pill }} {{ $links[0][2] ? 'bg-amber text-ink' : 'hover:bg-white/15' }}" @if($links[0][2]) aria-current="page" @endif>Home</a>
            <div class="relative" @mouseenter="tours = true" @mouseleave="tours = false">
                <button type="button" class="{{ $pill }} inline-flex items-center gap-1 hover:bg-white/15" @click="tours = !tours" :aria-expanded="tours" aria-haspopup="true">
                    Tours @icon('chevron-down', 'size-4')
                </button>
                <div x-cloak x-show="tours" x-transition.opacity class="absolute left-0 top-full w-60 pt-3">
                    <ul class="rounded-2xl bg-paper p-2 text-ink shadow-lift">
                        @foreach($navTypes as $t)
                            <li><a href="{{ route('packages.index', ['type' => $t->slug]) }}" class="block rounded-xl px-4 py-2.5 font-semibold hover:bg-cream">{{ $t->name }}</a></li>
                        @endforeach
                        <li><a href="{{ route('packages.index') }}" class="block rounded-xl px-4 py-2.5 text-slate hover:bg-cream">All packages</a></li>
                    </ul>
                </div>
            </div>
            @foreach(array_slice($links, 1) as [$label, $url, $active])
                <a href="{{ $url }}" class="{{ $pill }} {{ $active ? 'bg-amber text-ink' : 'hover:bg-white/15' }}" @if($active) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            <button type="button" @click="$store.modal.open()"
                    class="hidden min-h-11 items-center gap-2 rounded-full bg-paper py-1 pl-5 pr-1 font-display text-[0.95rem] font-semibold text-ink shadow-card hover:bg-white sm:inline-flex">
                Get a free quote
                <span class="grid size-9 place-items-center rounded-full bg-amber" aria-hidden="true">@icon('arrow-up-right', 'size-4')</span>
            </button>
            <button type="button" class="grid size-11 place-items-center rounded-full bg-ink/70 text-white shadow-card backdrop-blur-md lg:hidden" @click="open = !open" :aria-expanded="open" aria-controls="mobile-nav" aria-label="Menu">
                @icon('menu')
            </button>
        </div>
    </div>

    <nav id="mobile-nav" x-cloak x-show="open" x-transition.opacity class="absolute inset-x-0 top-full px-4 lg:hidden" aria-label="Mobile">
        <ul class="mx-auto max-w-md space-y-1 rounded-3xl bg-ink/95 p-3 text-white shadow-lift backdrop-blur-md">
            <li><a href="{{ route('home') }}" class="block rounded-2xl px-4 py-3 text-lg font-semibold hover:bg-white/10">Home</a></li>
            @foreach($navTypes as $t)
                <li><a href="{{ route('packages.index', ['type' => $t->slug]) }}" class="block rounded-2xl px-4 py-3 text-lg font-semibold hover:bg-white/10">{{ $t->name }}</a></li>
            @endforeach
            <li><a href="{{ route('packages.index') }}" class="block rounded-2xl px-4 py-3 text-lg font-semibold hover:bg-white/10">All packages</a></li>
            <li><a href="{{ route('about') }}" class="block rounded-2xl px-4 py-3 text-lg font-semibold hover:bg-white/10">About</a></li>
            <li><a href="{{ route('contact') }}" class="block rounded-2xl px-4 py-3 text-lg font-semibold hover:bg-white/10">Contact</a></li>
            <li class="pt-1"><x-button class="w-full" @click="open = false; $store.modal.open()">Start Your Adventure</x-button></li>
        </ul>
    </nav>
</header>
