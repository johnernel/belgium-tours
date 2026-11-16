@php
    $navTypes = \App\Models\TourType::active()->get();
    $links = [
        ['Packages', route('packages.index'), request()->routeIs('packages.*')],
        ['About', route('about'), request()->routeIs('about')],
        ['Contact', route('contact'), request()->routeIs('contact')],
    ];
    // Plain black text, no background and no radius. The current page is marked with an amber underline.
    $item = 'nav-link relative inline-flex min-h-11 items-center px-4 text-[0.95rem] font-semibold text-black hover:text-black/70';
    $current = 'after:absolute after:inset-x-4 after:bottom-1.5 after:h-0.5 after:bg-amber';
    // Pages with a photo hero (@section('hero')) start transparent with white text; see app.css.
    $overlay = $__env->hasSection('hero');
@endphp
<header class="sticky top-0 z-40 bg-bar transition-colors duration-200 {{ $overlay ? 'nav-overlay' : '' }}" :class="open && 'is-open'" x-data="{ open: false, tours: false }" @keydown.escape.window="open = false; tours = false">
    <div class="container-page flex h-[4.5rem] items-center justify-between gap-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            @if($site->logo)
                {{-- A logo uploaded in Admin > Settings wins; at the top of photo-hero pages it is turned white by CSS --}}
                <img src="{{ asset($site->logo) }}" alt="{{ $site->brand }}" class="nav-logo-upload h-10 w-auto" width="160" height="40">
            @else
                {{-- Default logo. The white-text version replaces it while the header is transparent at the top of the page. --}}
                <img src="{{ asset('images/logo.webp') }}" alt="{{ $site->brand }} home" class="logo-default h-11 w-auto sm:h-14" width="556" height="152">
                <img src="{{ asset('images/logo-white.webp') }}" alt="" aria-hidden="true" class="logo-white h-11 w-auto sm:h-14" width="556" height="152">
            @endif
        </a>

        <nav class="hidden items-center lg:flex" aria-label="Main">
            <a href="{{ route('home') }}" class="{{ $item }} {{ request()->routeIs('home') ? $current : '' }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>
            <div class="relative" @mouseenter="tours = true" @mouseleave="tours = false">
                <button type="button" class="{{ $item }} gap-1 {{ request()->routeIs('tours.*') ? $current : '' }}" @click="tours = !tours" :aria-expanded="tours" aria-haspopup="true">
                    Tour Types @icon('chevron-down', 'size-4')
                </button>
                <div x-cloak x-show="tours" x-transition.opacity class="absolute left-0 top-full w-60 pt-1">
                    <ul class="rounded-2xl bg-paper p-2 text-ink shadow-lift ring-1 ring-ink/10">
                        @foreach($navTypes as $t)
                            <li><a href="{{ route('tours.show', $t->slug) }}" class="block rounded-xl px-4 py-2.5 font-semibold hover:bg-cream" @if(request()->is('tours/'.$t->slug)) aria-current="page" @endif>{{ $t->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @foreach($links as [$label, $url, $active])
                <a href="{{ $url }}" class="{{ $item }} {{ $active ? $current : '' }}" @if($active) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            <x-button variant="cta" size="sm" class="hidden sm:inline-flex" @click="$store.modal.open()">Get a free quote</x-button>
            <button type="button" class="nav-menu-btn grid size-11 place-items-center rounded-full text-black hover:bg-black/5 lg:hidden" @click="open = !open" :aria-expanded="open" aria-controls="mobile-nav" aria-label="Menu">
                @icon('menu')
            </button>
        </div>
    </div>

    <nav id="mobile-nav" x-cloak x-show="open" x-transition.opacity class="bg-bar lg:hidden" aria-label="Mobile">
        <ul class="container-page space-y-1 py-3">
            <li><a href="{{ route('home') }}" class="block px-1 py-3 text-lg font-semibold text-black">Home</a></li>
            <li class="px-1 pt-2 text-sm font-semibold text-slate">Tour Types</li>
            @foreach($navTypes as $t)
                <li><a href="{{ route('tours.show', $t->slug) }}" class="block px-4 py-2.5 text-lg font-semibold text-black">{{ $t->name }}</a></li>
            @endforeach
            <li><a href="{{ route('packages.index') }}" class="block px-1 py-3 text-lg font-semibold text-black">Packages</a></li>
            <li><a href="{{ route('about') }}" class="block px-1 py-3 text-lg font-semibold text-black">About</a></li>
            <li><a href="{{ route('contact') }}" class="block px-1 py-3 text-lg font-semibold text-black">Contact</a></li>
            <li class="pt-2"><x-button variant="cta" class="w-full" @click="open = false; $store.modal.open()">Start Your Adventure</x-button></li>
        </ul>
    </nav>
</header>
