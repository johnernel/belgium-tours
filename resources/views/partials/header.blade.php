@php
    $navTypes = \App\Models\TourType::active()->get();
@endphp
<header class="sticky top-0 z-40 border-b border-ink/10 bg-cream/95 backdrop-blur" x-data="{ open: false, tours: false }" @keydown.escape.window="open = false; tours = false">
    <div class="container-page flex h-16 items-center justify-between gap-4 sm:h-[4.5rem]">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            @if($site->logo)
                <img src="{{ asset($site->logo) }}" alt="{{ $site->brand }}" class="h-10 w-auto" width="160" height="40">
            @else
                <span class="grid size-10 place-items-center rounded-xl bg-ink text-amber" aria-hidden="true">@icon('compass', 'size-6')</span>
                <span class="font-display text-xl font-semibold leading-none tracking-tight">Go Belgium<span class="block text-[0.7rem] font-medium tracking-[0.22em] text-slate">TOURS</span><span class="sr-only"> home</span></span>
            @endif
        </a>

        <nav class="hidden items-center gap-1 lg:flex" aria-label="Main">
            <a href="{{ route('home') }}" class="rounded-full px-4 py-2 font-semibold hover:bg-ink/5">Home</a>
            <div class="relative" @mouseenter="tours = true" @mouseleave="tours = false">
                <button type="button" class="inline-flex items-center gap-1 rounded-full px-4 py-2 font-semibold hover:bg-ink/5" @click="tours = !tours" :aria-expanded="tours" aria-haspopup="true">
                    Tours @icon('chevron-down', 'size-4')
                </button>
                <div x-cloak x-show="tours" x-transition.opacity class="absolute left-0 top-full w-60 pt-2">
                    <ul class="rounded-2xl bg-paper p-2 shadow-lift">
                        @foreach($navTypes as $t)
                            <li><a href="{{ route('packages.index', ['type' => $t->slug]) }}" class="block rounded-xl px-4 py-2.5 font-semibold hover:bg-cream">{{ $t->name }}</a></li>
                        @endforeach
                        <li><a href="{{ route('packages.index') }}" class="block rounded-xl px-4 py-2.5 text-slate hover:bg-cream">All packages</a></li>
                    </ul>
                </div>
            </div>
            <a href="{{ route('packages.index') }}" class="rounded-full px-4 py-2 font-semibold hover:bg-ink/5">Packages</a>
            <a href="{{ route('about') }}" class="rounded-full px-4 py-2 font-semibold hover:bg-ink/5">About</a>
            <a href="{{ route('contact') }}" class="rounded-full px-4 py-2 font-semibold hover:bg-ink/5">Contact</a>
        </nav>

        <div class="flex items-center gap-2">
            <x-button size="sm" class="hidden sm:inline-flex" @click="$store.modal.open()">Get a free quote</x-button>
            <button type="button" class="grid size-11 place-items-center rounded-full hover:bg-ink/5 lg:hidden" @click="open = !open" :aria-expanded="open" aria-controls="mobile-nav" aria-label="Menu">
                @icon('menu')
            </button>
        </div>
    </div>

    <nav id="mobile-nav" x-cloak x-show="open" x-transition.opacity class="border-t border-ink/10 bg-cream lg:hidden" aria-label="Mobile">
        <ul class="container-page space-y-1 py-3">
            <li><a href="{{ route('home') }}" class="block rounded-xl px-3 py-3 text-lg font-semibold">Home</a></li>
            @foreach($navTypes as $t)
                <li><a href="{{ route('packages.index', ['type' => $t->slug]) }}" class="block rounded-xl px-3 py-3 text-lg font-semibold">{{ $t->name }}</a></li>
            @endforeach
            <li><a href="{{ route('packages.index') }}" class="block rounded-xl px-3 py-3 text-lg font-semibold">All packages</a></li>
            <li><a href="{{ route('about') }}" class="block rounded-xl px-3 py-3 text-lg font-semibold">About</a></li>
            <li><a href="{{ route('contact') }}" class="block rounded-xl px-3 py-3 text-lg font-semibold">Contact</a></li>
            <li class="pt-2"><x-button class="w-full" @click="open = false; $store.modal.open()">Start Your Adventure</x-button></li>
        </ul>
    </nav>
</header>
