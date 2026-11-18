{{-- Header for the "Homepage Test" design: a slim contact strip above the main bar, transparent over the hero and solid once scrolled.
     Same Go Belgium logo as the real site (white version while transparent, see app.css). --}}
@php
    $navTypes = \App\Models\TourType::active()->get();
    $aboutLinks = [['About Us', route('about')], ['Meet Our Team', route('team')], ['Testimonials', route('testimonials')], ['FAQs', route('faqs')]];
    $link = 'nav-link relative inline-flex min-h-11 items-center gap-1 px-4 text-base font-medium xl:px-5 text-ink hover:text-ink/70';
@endphp
<header class="nav-overlay sticky top-0 z-40 bg-bar transition-colors duration-200" :class="open && 'is-open'" x-data="{ open: false, menu: null }" @keydown.escape.window="open = false; menu = null" @mouseleave="menu = null">
    {{-- Contact strip: solid charcoal band, phone and email on the left, outlined "Enquire Now" on the right (hidden once scrolled) --}}
    <div class="hidden bg-ink text-white [.is-scrolled_&]:hidden lg:block">
        <div class="container-page flex h-16 items-center justify-between gap-6">
            <ul class="flex items-center gap-8">
                @if($site->phone)<li><a class="inline-flex items-center gap-2.5 hover:text-amber" href="tel:{{ $site->phone_href }}">@icon('phone', 'size-5 text-amber') {{ $site->phone }}</a></li>@endif
                @if($site->email)<li><a class="inline-flex items-center gap-2.5 hover:text-amber" href="mailto:{{ $site->email }}">@icon('mail', 'size-5 text-amber') {{ $site->email }}</a></li>@endif
                @if(! $site->phone && ! $site->email)<li class="inline-flex items-center gap-2.5">@icon('shield', 'size-5 text-amber') Free, no-obligation quote. Tailor-made trips across Belgium.</li>@endif
            </ul>
            <button type="button" @click="$store.modal.open()" class="inline-flex min-h-11 items-center border-2 border-white px-7 font-semibold text-white transition hover:bg-white hover:text-ink">Enquire Now</button>
        </div>
    </div>

    <div class="container-page flex h-[4.5rem] items-center justify-between gap-3 lg:h-[5.5rem]">
        <a href="{{ route('home') }}" class="flex items-center">
            @if($site->logo)
                <img src="{{ asset($site->logo) }}" alt="{{ $site->brand }}" class="nav-logo-upload h-10 w-auto lg:h-12" width="160" height="40">
            @else
                <img src="{{ asset('images/logo.webp') }}" alt="{{ $site->brand }} home" class="logo-default h-11 w-auto sm:h-14 lg:h-[4.25rem]" width="556" height="152">
                <img src="{{ asset('images/logo-white.webp') }}" alt="" aria-hidden="true" class="logo-white h-11 w-auto sm:h-14 lg:h-[4.25rem]" width="556" height="152">
            @endif
        </a>
        <nav class="ml-auto hidden items-center lg:flex" aria-label="Main">
            <a href="{{ route('home') }}" class="{{ $link }}">Home</a>
            <div class="relative" @mouseenter="menu = 'tours'">
                <button type="button" class="{{ $link }}" :aria-expanded="menu === 'tours'" aria-haspopup="true" @click="menu = menu === 'tours' ? null : 'tours'">Tour Types @icon('chevron-down', 'size-4')</button>
                <ul x-cloak x-show="menu === 'tours'" x-transition.opacity.duration.150ms class="absolute left-0 top-full z-50 mt-1 w-64 overflow-hidden rounded-2xl bg-paper py-2 text-ink shadow-lift">
                    @foreach($navTypes as $t)<li><a href="{{ route('tours.show', $t->slug) }}" class="block px-5 py-2.5 font-semibold hover:bg-cream">{{ $t->name }}</a></li>@endforeach
                </ul>
            </div>
            <a href="{{ route('destinations.index') }}" @mouseenter="menu = null" class="{{ $link }}">Destinations</a>
            <a href="{{ route('packages.index') }}" @mouseenter="menu = null" class="{{ $link }}">Packages</a>
            <div class="relative" @mouseenter="menu = 'about'">
                <button type="button" class="{{ $link }}" :aria-expanded="menu === 'about'" aria-haspopup="true" @click="menu = menu === 'about' ? null : 'about'">About Us @icon('chevron-down', 'size-4')</button>
                <ul x-cloak x-show="menu === 'about'" x-transition.opacity.duration.150ms class="absolute left-0 top-full z-50 mt-1 w-56 overflow-hidden rounded-2xl bg-paper py-2 text-ink shadow-lift">
                    @foreach($aboutLinks as [$l, $u])<li><a href="{{ $u }}" class="block px-5 py-2.5 font-semibold hover:bg-cream">{{ $l }}</a></li>@endforeach
                </ul>
            </div>
            <a href="{{ route('contact') }}" @mouseenter="menu = null" class="{{ $link }}">Contact Us</a>
        </nav>

        <div class="flex items-center gap-2">
            <x-button square variant="cta" size="sm" class="hidden sm:inline-flex lg:hidden [.is-scrolled_&]:lg:inline-flex" @click="$store.modal.open()">Enquire Now</x-button>
            <button type="button" class="nav-menu-btn grid size-11 place-items-center rounded-full text-ink hover:bg-ink/5 lg:hidden" @click="open = !open" :aria-expanded="open" aria-controls="mobile-nav-test" aria-label="Menu">@icon('menu')</button>
        </div>
    </div>

    <nav id="mobile-nav-test" x-cloak x-show="open" x-transition.opacity class="bg-bar lg:hidden" aria-label="Mobile">
        <ul class="container-page space-y-1 py-3">
            <li><a href="{{ route('home') }}" class="block px-1 py-3 text-lg font-semibold text-ink">Home</a></li>
            <li class="px-1 pt-2 text-sm font-semibold text-slate">Tour Types</li>
            @foreach($navTypes as $t)<li><a href="{{ route('tours.show', $t->slug) }}" class="block px-4 py-2.5 text-lg font-semibold text-ink">{{ $t->name }}</a></li>@endforeach
            <li><a href="{{ route('destinations.index') }}" class="block px-1 py-3 text-lg font-semibold text-ink">Destinations</a></li>
            <li><a href="{{ route('packages.index') }}" class="block px-1 py-3 text-lg font-semibold text-ink">Packages</a></li>
            <li class="px-1 pt-2 text-sm font-semibold text-slate">About Us</li>
            @foreach($aboutLinks as [$l, $u])<li><a href="{{ $u }}" class="block px-4 py-2.5 text-lg font-semibold text-ink">{{ $l }}</a></li>@endforeach
            <li><a href="{{ route('contact') }}" class="block px-1 py-3 text-lg font-semibold text-ink">Contact Us</a></li>
            <li class="pt-2"><x-button square variant="cta" class="w-full" @click="open = false; $store.modal.open()">Enquire Now</x-button></li>
        </ul>
    </nav>
</header>
