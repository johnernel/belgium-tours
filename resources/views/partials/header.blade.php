@php
    // Tour types for the mega menu, each with its published packages (featured first; the menu shows the first 3).
    $navTypes = \App\Models\TourType::active()->with(['packages' => fn ($q) => $q->published()->orderByDesc('is_featured')->orderBy('sort')])->get();
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
<header class="sticky top-0 z-40 bg-bar transition-colors duration-200 {{ $overlay ? 'nav-overlay' : '' }}" :class="open && 'is-open'" x-data="{ open: false, tours: false }" @keydown.escape.window="open = false; tours = false" @mouseleave="tours = false" @focusout="if (! $el.contains($event.relatedTarget)) tours = false">
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
            <a href="{{ route('home') }}" @mouseenter="tours = false" class="{{ $item }} {{ request()->routeIs('home') ? $current : '' }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>
            {{-- Tour Types mega menu: the panel is positioned against the (sticky) header, so it spans the full width --}}
            <div @mouseenter="tours = true" @click.outside="tours = false">
                <button type="button" class="{{ $item }} gap-1 {{ request()->routeIs('tours.*') ? $current : '' }}" @click="matchMedia('(hover: hover)').matches ? tours = true : tours = !tours" :aria-expanded="tours" aria-controls="mega-tours" aria-haspopup="true">
                    Tour Types @icon('chevron-down', 'size-4 transition-transform duration-200')
                </button>

                <div id="mega-tours" x-cloak x-show="tours" x-transition.opacity.duration.150ms class="absolute inset-x-0 top-full z-50">
                    {{-- Full-bleed panel (edge to edge); the columns line up with the page content --}}
                    <div class="border-t border-ink/10 bg-paper text-ink shadow-lift">
                        <div>
                            <div class="container-page grid gap-x-10 gap-y-6 py-7 lg:grid-cols-3">
                                @foreach($navTypes as $t)
                                    @php $featured = $t->packages->take(2); @endphp
                                    <section aria-labelledby="mega-{{ $t->slug }}" class="flex min-w-0 flex-col">
                                        {{-- Photo tile with the tour type name over it --}}
                                        <a href="{{ route('tours.show', $t->slug) }}" class="group relative block aspect-[16/8] overflow-hidden rounded-2xl bg-slate" @if(request()->is('tours/'.$t->slug)) aria-current="page" @endif>
                                            @if($t->image)
                                                <x-photo :base="$t->image" :alt="''" sizes="(min-width:1024px) 26vw, 90vw" fetchpriority="low" :ratio="[1600, 800]" class="size-full transition-transform duration-500 group-hover:scale-105" />
                                            @endif
                                            <span class="absolute inset-0 bg-gradient-to-t from-ink/85 via-ink/25 to-transparent" aria-hidden="true"></span>
                                            <span class="absolute inset-x-4 bottom-3 flex items-end justify-between gap-3">
                                                <span id="mega-{{ $t->slug }}" class="font-serif text-2xl leading-tight text-white">{{ $t->name }}</span>
                                                <span class="mb-1 grid size-8 shrink-0 place-items-center rounded-full bg-aqua text-ink transition group-hover:bg-white" aria-hidden="true">@icon('arrow-right', 'size-4')</span>
                                            </span>
                                        </a>
                                        <p class="mt-3 truncate text-sm text-slate">{{ $t->menu_text ?: \Illuminate\Support\Str::limit((string) $t->summary, 40) }}</p>

                                        {{-- Featured trips --}}
                                        @if($featured->isNotEmpty())
                                            <p class="mt-4 flex items-center gap-3 text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-amber-ink"><span class="h-px w-6 bg-amber" aria-hidden="true"></span>Featured trips</p>
                                            <ul class="mt-2 divide-y divide-ink/10">
                                                @foreach($featured as $p)
                                                    <li>
                                                        <a href="{{ route('packages.show', $p->slug) }}" class="group/row flex items-center gap-3 py-2.5">
                                                            <span class="size-12 shrink-0 overflow-hidden rounded-xl bg-sky-soft">
                                                                @if($p->hero_image)<x-photo :base="$p->hero_image" :alt="''" :widths="[480, 800]" sizes="48px" :ratio="[480, 480]" class="size-full" />@endif
                                                            </span>
                                                            <span class="min-w-0 flex-1">
                                                                <span class="block truncate text-[0.95rem] font-semibold leading-snug group-hover/row:text-amber-ink">{{ $p->title }}</span>
                                                                <span class="block truncate text-xs text-slate">{{ $p->nights > 0 ? $p->nights.' nights' : '1 day' }}{{ $p->route ? ' · '.$p->route : '' }}</span>
                                                            </span>
                                                            @icon('arrow-up-right', 'size-4 shrink-0 text-slate opacity-0 transition group-hover/row:opacity-100 group-focus-visible/row:opacity-100')
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif

                                        <a href="{{ route('packages.index', ['type' => $t->slug]) }}" class="mt-auto inline-flex min-h-11 items-center gap-2 pt-3 text-xs font-semibold uppercase tracking-[0.16em] text-ink underline decoration-amber decoration-2 underline-offset-4 hover:text-amber-ink">
                                            View all<span class="sr-only"> {{ $t->name }}</span> @icon('arrow-right', 'size-3.5')
                                        </a>
                                    </section>
                                @endforeach
                            </div>
                            <div class="border-t border-ink/10 bg-cream">
                                <div class="container-page flex items-center justify-between gap-4 py-3">
                                    <p class="text-sm text-ink">Not sure which way to travel suits you? Tell us what you like and we will suggest one.</p>
                                    <x-button variant="cta" size="sm" @click="tours = false; $store.modal.open()">Get a free quote</x-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @foreach($links as [$label, $url, $active])
                <a href="{{ $url }}" @mouseenter="tours = false" class="{{ $item }} {{ $active ? $current : '' }}" @if($active) aria-current="page" @endif>{{ $label }}</a>
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
