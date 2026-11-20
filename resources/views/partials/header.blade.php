@php
    // Tour types for the mega menu, each with its published packages (featured first; the menu shows the first 3).
    $navTypes = \App\Models\TourType::active()->with(['packages' => fn ($q) => $q->published()->orderByDesc('is_featured')->orderBy('sort')])->get();
    // "About Us" mega menu: image cards (bases are in public/images/photos). Descriptions are short and general.
    $aboutItems = [
        ['About Us', route('about'), 'images/photos/bruges-belfry', 'Who we are and how we plan your Belgium trip.'],
        ['Meet Our Team', route('team'), 'images/photos/countryside', 'The people who plan and look after your journey.'],
        ['Testimonials', route('testimonials'), 'images/photos/ghent-graslei', 'Read what other travellers say about their trips.'],
        ['FAQs', route('faqs'), 'images/photos/city-leuven', 'Quick answers to the questions we hear most.'],
    ];
    $aboutActive = request()->routeIs('about', 'team', 'testimonials', 'faqs');
    $links = [
        ['Packages', route('packages.index'), request()->routeIs('packages.*')],
    ];
    // Plain black text, no background and no radius. The current page is marked with an amber underline.
    $item = 'nav-link relative inline-flex min-h-11 items-center px-4 text-[0.95rem] font-semibold text-black hover:text-black/70';
    $current = 'after:absolute after:inset-x-4 after:bottom-1.5 after:h-0.5 after:bg-amber';
    // Pages with a photo hero (@section('hero')) start transparent with white text; see app.css.
    $overlay = $__env->hasSection('hero');
@endphp
<header class="sticky top-0 z-40 bg-bar transition-colors duration-200 {{ $overlay ? 'nav-overlay' : '' }}" :class="open && 'is-open'" x-data="{ open: false, mega: null }" @keydown.escape.window="open = false; mega = null" @mouseleave="mega = null" @focusout="if (! $el.contains($event.relatedTarget)) mega = null">
    <div class="container-page flex h-[4.5rem] items-center justify-between gap-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5" aria-label="{{ $site->brand }} home">
            @if($site->logo)
                {{-- A logo uploaded in Admin > Settings wins; at the top of photo-hero pages it is turned white by CSS --}}
                <img src="{{ asset($site->logo) }}" alt="{{ $site->brand }}" class="nav-logo-upload h-10 w-auto" width="160" height="40">
            @else
                {{-- Default logo. The white-text version replaces it while the header is transparent at the top of the page. --}}
                <img src="{{ asset('images/logo.webp') }}?v={{ @filemtime(public_path('images/logo.webp')) }}" alt="{{ $site->brand }} home" class="logo-default h-11 w-auto sm:h-14" width="484" height="152">
                <img src="{{ asset('images/logo-white.webp') }}?v={{ @filemtime(public_path('images/logo-white.webp')) }}" alt="" aria-hidden="true" class="logo-white h-11 w-auto sm:h-14" width="484" height="152">
            @endif
        </a>

        <nav class="hidden items-center lg:flex" aria-label="Main">
            <a href="{{ route('home') }}" @mouseenter="mega = null" class="{{ $item }} {{ request()->routeIs('home') ? $current : '' }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>
            {{-- Tour Types mega menu: the panel is positioned against the (sticky) header, so it spans the full width --}}
            <div @mouseenter="mega = 'tours'" @click.outside="mega === 'tours' && (mega = null)">
                <button type="button" class="{{ $item }} gap-1 {{ request()->routeIs('tours.*') ? $current : '' }}" @click="matchMedia('(hover: hover)').matches ? mega = 'tours' : mega = (mega === 'tours' ? null : 'tours')" :aria-expanded="mega === 'tours'" aria-controls="mega-tours" aria-haspopup="true">
                    Tour Types @icon('chevron-down', 'size-4 transition-transform duration-200')
                </button>

                <div id="mega-tours" x-cloak x-show="mega === 'tours'" x-transition.opacity.duration.150ms class="absolute inset-x-0 top-full z-50">
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
                                    <x-button variant="cta" size="sm" @click="mega = null; $store.modal.open()">Get a free quote</x-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @foreach($links as [$label, $url, $active])
                <a href="{{ $url }}" @mouseenter="mega = null" class="{{ $item }} {{ $active ? $current : '' }}" @if($active) aria-current="page" @endif>{{ $label }}</a>
            @endforeach

            {{-- About Us mega menu (photo panel + four image cards), full width like the Tour Types one --}}
            <div @mouseenter="mega = 'about'" @click.outside="mega === 'about' && (mega = null)">
                <button type="button" class="{{ $item }} gap-1 {{ $aboutActive ? $current : '' }}" @click="matchMedia('(hover: hover)').matches ? mega = 'about' : mega = (mega === 'about' ? null : 'about')" :aria-expanded="mega === 'about'" aria-controls="mega-about" aria-haspopup="true">
                    About Us @icon('chevron-down', 'size-4 transition-transform duration-200')
                </button>

                <div id="mega-about" x-cloak x-show="mega === 'about'" x-transition.opacity.duration.150ms class="absolute inset-x-0 top-full z-50">
                    <div class="border-t border-ink/10 bg-cream text-ink shadow-lift">
                        <div class="grid lg:grid-cols-[20rem_1fr] xl:grid-cols-[24rem_1fr]">
                            {{-- Left: photo with the intro --}}
                            <div class="on-dark relative isolate flex min-h-[20rem] flex-col justify-end overflow-hidden bg-ink p-8 text-white">
                                <x-photo base="images/photos/gravensteen" alt="" sizes="24rem" fetchpriority="low" :ratio="[1600, 1044]" class="absolute inset-0 -z-20 size-full" />
                                <span class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/90 via-ink/55 to-ink/25" aria-hidden="true"></span>
                                <p class="flex items-center gap-4 text-xs font-semibold uppercase tracking-[0.22em] text-white">
                                    <span class="h-px w-10 bg-amber" aria-hidden="true"></span>Get to know us
                                </p>
                                <p class="mt-4 font-serif text-3xl font-light leading-tight text-white">@heading('Plan *With Peace of Mind*')</p>
                                <p class="mt-4 text-[0.95rem] leading-relaxed text-white/95">Learn about our team, read what travellers say and find the answers that make your Belgium trip smooth and stress-free.</p>
                            </div>

                            {{-- Right: the four pages --}}
                            <ul class="grid gap-5 p-6 sm:grid-cols-2 xl:grid-cols-4 xl:p-8">
                                @foreach($aboutItems as [$label, $url, $img, $text])
                                    <li>
                                        <a href="{{ $url }}" class="group flex h-full flex-col overflow-hidden rounded-2xl bg-paper shadow-card transition hover:-translate-y-0.5 hover:shadow-lift" @click="mega = null" @if(url()->current() === $url) aria-current="page" @endif>
                                            <span class="relative block aspect-[4/3] overflow-hidden bg-sky-soft">
                                                <x-photo :base="$img" alt="" sizes="(min-width:1280px) 18vw, 40vw" fetchpriority="low" :ratio="[1600, 1200]" class="size-full transition-transform duration-500 group-hover:scale-105" />
                                            </span>
                                            <span class="flex flex-1 flex-col p-5">
                                                <span class="font-serif text-xl leading-tight">{{ $label }}</span>
                                                <span class="mt-2 text-sm leading-relaxed text-slate">{{ $text }}</span>
                                                <span class="mt-auto inline-flex items-center gap-2 pt-4 text-xs font-semibold uppercase tracking-[0.16em] text-amber-ink">Learn more @icon('arrow-right', 'size-3.5 transition-transform group-hover:translate-x-1')</span>
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <a href="{{ route('contact') }}" @mouseenter="mega = null" class="{{ $item }} {{ request()->routeIs('contact') ? $current : '' }}" @if(request()->routeIs('contact')) aria-current="page" @endif>Contact</a>
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
            <li class="px-1 pt-2 text-sm font-semibold text-slate">Get to know us</li>
            @foreach($aboutItems as [$label, $url])
                <li><a href="{{ $url }}" class="block px-4 py-2.5 text-lg font-semibold text-black">{{ $label }}</a></li>
            @endforeach
            <li><a href="{{ route('contact') }}" class="block px-1 py-3 text-lg font-semibold text-black">Contact</a></li>
            <li class="pt-2"><x-button variant="cta" class="w-full" @click="open = false; $store.modal.open()">Start Your Adventure</x-button></li>
        </ul>
    </nav>
</header>
