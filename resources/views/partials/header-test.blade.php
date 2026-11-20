{{-- Header for the "Homepage Test" design: a slim contact strip above the main bar, transparent over the hero and solid once scrolled.
     Same Go Belgium logo as the real site (white version while transparent, see app.css). --}}
@php
    $navTypes = \App\Models\TourType::active()->with(['packages' => fn ($q) => $q->published()->orderByDesc('is_featured')->orderBy('sort')])->get();
    $aboutItems = [
        ['About Us', route('about'), 'images/photos/bruges-belfry', 'Who we are and how we plan your Belgium trip.'],
        ['Meet Our Team', route('team'), 'images/photos/countryside', 'The people who plan and look after your journey.'],
        ['Testimonials', route('testimonials'), 'images/photos/ghent-graslei', 'Read what other travellers say about their trips.'],
        ['FAQs', route('faqs'), 'images/photos/city-leuven', 'Quick answers to the questions we hear most.'],
    ];
    $aboutLinks = array_map(fn ($i) => [$i[0], $i[1]], $aboutItems);
    $link = 'nav-link relative inline-flex min-h-11 items-center gap-1 px-4 text-base font-medium xl:px-5 text-ink hover:text-ink/70';
@endphp
<header class="nav-overlay sticky top-0 z-40 bg-bar transition-colors duration-200 [&.is-open]:bg-white" :class="open && 'is-open'" x-data="{ open: false, menu: null }" @keydown.escape.window="open = false; menu = null" @mouseleave="menu = null" @focusout="if (! $el.contains($event.relatedTarget)) menu = null">
    {{-- Contact strip: solid charcoal band, phone and email on the left, outlined "Enquire Now" on the right (hidden once scrolled) --}}
    <div class="hidden bg-slate text-white [.is-scrolled_&]:hidden lg:block">
        <div class="container-page flex h-12 items-center justify-between gap-6">
            <ul class="flex items-center gap-6 text-sm">
                {{-- Contact details: the numbers saved in Admin > Settings win; until they are set, the placeholders below are shown --}}
                <li><a class="inline-flex items-center gap-2 hover:text-amber" href="tel:{{ $site->phone ? $site->phone_href : '+10000000000' }}">@icon('phone', 'size-4 text-amber') {{ $site->phone ?: '+1 000-000-0000' }}</a></li>
                <li><a class="inline-flex items-center gap-2 hover:text-amber" href="mailto:{{ $site->email ?: 'info@gobelgiumtrips.com' }}">@icon('mail', 'size-4 text-amber') {{ $site->email ?: 'info@gobelgiumtrips.com' }}</a></li>
            </ul>
            <button type="button" @click="$store.modal.open()" class="inline-flex min-h-8 items-center border border-white px-4 text-sm font-semibold text-white transition hover:bg-white hover:text-ink">Enquire Now</button>
        </div>
    </div>

    <div class="container-page flex h-[4.5rem] items-center justify-between gap-3 lg:h-[5.5rem]">
        <a href="{{ route('home') }}" class="flex items-center" aria-label="{{ $site->brand }} home">
            @if($site->logo)
                <img src="{{ asset($site->logo) }}" alt="{{ $site->brand }}" class="nav-logo-upload h-10 w-auto lg:h-12" width="160" height="40">
            @else
                <img src="{{ asset('images/logo.webp') }}" alt="{{ $site->brand }} home" class="logo-default h-11 w-auto sm:h-14 lg:h-[4.25rem]" width="556" height="152">
                <img src="{{ asset('images/logo-white.webp') }}" alt="" aria-hidden="true" class="logo-white h-11 w-auto sm:h-14 lg:h-[4.25rem]" width="556" height="152">
            @endif
        </a>
        <nav class="ml-auto hidden items-center lg:flex" aria-label="Main">
            <a href="{{ route('home') }}" class="{{ $link }}">Home</a>
            {{-- Tour Types mega menu: full-width panel anchored to the sticky header; one tile per tour type with its featured trips --}}
            <div @mouseenter="menu = 'tours'" @click.outside="menu === 'tours' && (menu = null)">
                <button type="button" class="{{ $link }}" :aria-expanded="menu === 'tours'" aria-controls="mega-tours-test" aria-haspopup="true" @click="matchMedia('(hover: hover)').matches ? menu = 'tours' : menu = (menu === 'tours' ? null : 'tours')">Tour Types @icon('chevron-down', 'size-4')</button>
                <div id="mega-tours-test" x-cloak x-show="menu === 'tours'" x-transition.opacity.duration.150ms class="absolute inset-x-0 top-full z-50">
                    <div class="border-t border-ink/10 bg-paper text-ink shadow-lift">
                        <div class="container-page grid gap-x-10 gap-y-6 py-7 lg:grid-cols-3">
                            @foreach($navTypes as $t)
                                @php $featured = $t->packages->take(2); @endphp
                                <section aria-labelledby="mt-{{ $t->slug }}" class="flex min-w-0 flex-col">
                                    <a href="{{ route('tours.show', $t->slug) }}" class="group relative block aspect-[16/8] overflow-hidden bg-slate" @click="menu = null">
                                        @if($t->image)<x-photo :base="$t->image" alt="" sizes="(min-width:1024px) 26vw, 90vw" fetchpriority="low" :ratio="[1600, 800]" class="size-full transition-transform duration-500 group-hover:scale-105" />@endif
                                        <span class="absolute inset-0 bg-gradient-to-t from-ink/85 via-ink/25 to-transparent" aria-hidden="true"></span>
                                        <span class="absolute inset-x-4 bottom-3 flex items-end justify-between gap-3">
                                            <span id="mt-{{ $t->slug }}" class="font-serif text-2xl leading-tight text-white">{{ $t->name }}</span>
                                            <span class="mb-1 grid size-8 shrink-0 place-items-center bg-aqua text-ink transition group-hover:bg-white" aria-hidden="true">@icon('arrow-right', 'size-4')</span>
                                        </span>
                                    </a>
                                    <p class="mt-3 truncate text-sm text-slate">{{ $t->menu_text ?: \Illuminate\Support\Str::limit((string) $t->summary, 40) }}</p>
                                    @if($featured->isNotEmpty())
                                        <p class="mt-4 flex items-center gap-3 text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-amber-ink"><span class="h-px w-6 bg-amber" aria-hidden="true"></span>Featured trips</p>
                                        <ul class="mt-2 divide-y divide-ink/10">
                                            @foreach($featured as $p)
                                                <li>
                                                    <a href="{{ route('packages.show', $p->slug) }}" class="group/row flex items-center gap-3 py-2.5" @click="menu = null">
                                                        <span class="size-12 shrink-0 overflow-hidden bg-sky-soft">@if($p->hero_image)<x-photo :base="$p->hero_image" alt="" :widths="[480, 800]" sizes="48px" :ratio="[480, 480]" class="size-full" />@endif</span>
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
                                    <a href="{{ route('packages.index', ['type' => $t->slug]) }}" class="mt-auto inline-flex min-h-11 items-center gap-2 pt-3 text-xs font-semibold uppercase tracking-[0.16em] text-ink underline decoration-amber decoration-2 underline-offset-4 hover:text-amber-ink" @click="menu = null">View all<span class="sr-only"> {{ $t->name }}</span> @icon('arrow-right', 'size-3.5')</a>
                                </section>
                            @endforeach
                        </div>
                        <div class="border-t border-ink/10 bg-cream">
                            <div class="container-page flex items-center justify-between gap-4 py-3">
                                <p class="text-sm text-ink">Not sure which way to travel suits you? Tell us what you like and we will suggest one.</p>
                                <x-button square variant="cta" size="sm" @click="menu = null; $store.modal.open()">Get a free quote</x-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- About Us mega menu: photo panel on the left, four image cards on the right --}}
            <div @mouseenter="menu = 'about'" @click.outside="menu === 'about' && (menu = null)">
                <button type="button" class="{{ $link }}" :aria-expanded="menu === 'about'" aria-controls="mega-about-test" aria-haspopup="true" @click="matchMedia('(hover: hover)').matches ? menu = 'about' : menu = (menu === 'about' ? null : 'about')">About Us @icon('chevron-down', 'size-4')</button>
                <div id="mega-about-test" x-cloak x-show="menu === 'about'" x-transition.opacity.duration.150ms class="absolute inset-x-0 top-full z-50">
                    <div class="border-t border-ink/10 bg-cream text-ink shadow-lift">
                        <div class="grid lg:grid-cols-[20rem_1fr] xl:grid-cols-[24rem_1fr]">
                            <div class="on-dark relative isolate flex min-h-[20rem] flex-col justify-end overflow-hidden bg-ink p-8 text-white">
                                <x-photo base="images/photos/gravensteen" alt="" sizes="24rem" fetchpriority="low" :ratio="[1600, 1044]" class="absolute inset-0 -z-20 size-full" />
                                <span class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/90 via-ink/55 to-ink/25" aria-hidden="true"></span>
                                <p class="flex items-center gap-4 text-xs font-semibold uppercase tracking-[0.22em] text-white"><span class="h-px w-10 bg-amber" aria-hidden="true"></span>Get to know us</p>
                                <p class="mt-4 font-serif text-3xl font-light leading-tight text-white">@heading('Plan *With Peace of Mind*')</p>
                                <p class="mt-4 text-[0.95rem] leading-relaxed text-white/95">Learn about our team, read what travellers say and find the answers that make your Belgium trip smooth and stress-free.</p>
                            </div>
                            <ul class="grid gap-5 p-6 sm:grid-cols-2 xl:grid-cols-4 xl:p-8">
                                @foreach($aboutItems as [$label, $url, $img, $text])
                                    <li>
                                        <a href="{{ $url }}" class="group flex h-full flex-col overflow-hidden bg-paper shadow-card transition hover:-translate-y-0.5 hover:shadow-lift" @click="menu = null">
                                            <span class="relative block aspect-[4/3] overflow-hidden bg-sky-soft"><x-photo :base="$img" alt="" sizes="(min-width:1280px) 18vw, 40vw" fetchpriority="low" :ratio="[1600, 1200]" class="size-full transition-transform duration-500 group-hover:scale-105" /></span>
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
            </div>            <a href="{{ route('contact') }}" @mouseenter="menu = null" class="{{ $link }}">Contact Us</a>
        </nav>

        <div class="flex items-center gap-2">
            <x-button square variant="cta" size="sm" class="max-sm:!hidden lg:hidden [.is-scrolled_&]:lg:inline-flex" @click="$store.modal.open()">Enquire Now</x-button>
            <button type="button" class="nav-menu-btn grid size-11 place-items-center rounded-full text-ink hover:bg-ink/5 lg:hidden" @click="open = !open" :aria-expanded="open" aria-controls="mobile-nav-test" aria-label="Menu">@icon('menu')</button>
        </div>
    </div>

    {{-- Phone menu (layout copied from the client's reference): big uppercase links with hairline dividers, Tour Types and About Us open into photo cards,
         an "All packages" button and a full-width quote button. Only one section is open at a time. --}}
    <nav id="mobile-nav-test" x-cloak x-show="open" x-transition.opacity x-data="{ sub: null }" class="max-h-[calc(100dvh-4.5rem)] overflow-y-auto bg-white lg:hidden" aria-label="Mobile">
        <ul class="container-page pb-5 pt-1">
            <li class="border-b border-ink/10"><a href="{{ route('home') }}" class="flex min-h-12 items-center text-[0.95rem] font-semibold uppercase tracking-[0.08em] text-ink">Home</a></li>

            <li class="border-b border-ink/10">
                <button type="button" @click="sub = sub === 'tours' ? null : 'tours'" :aria-expanded="sub === 'tours'" aria-controls="m-sub-tours" class="flex min-h-12 w-full items-center justify-between text-left text-[0.95rem] font-semibold uppercase tracking-[0.08em] text-ink [&[aria-expanded=true]>svg]:rotate-180">
                    Tour Types @icon('chevron-down', 'size-5 shrink-0 transition-transform duration-200')
                </button>
                <div id="m-sub-tours" class="grid transition-[grid-template-rows] duration-300 ease-out motion-reduce:transition-none" style="grid-template-rows: 0fr" :style="sub === 'tours' ? 'grid-template-rows: 1fr' : 'grid-template-rows: 0fr'" :inert="sub !== 'tours'" inert><div class="min-h-0 space-y-3 overflow-hidden"><div class="space-y-3 pb-4">
                    @foreach($navTypes as $t)
                        <a href="{{ route('tours.show', $t->slug) }}" class="flex items-center gap-4 border border-amber/40 bg-white p-2.5">
                            <span class="size-[4.5rem] shrink-0 overflow-hidden bg-sky-soft">@if($t->image)<x-photo :base="$t->image" alt="" :widths="[480, 800]" sizes="72px" :ratio="[480, 480]" class="size-full" />@endif</span>
                            <span class="min-w-0">
                                <span class="block font-display text-[1.15rem] leading-tight text-ink">{{ $t->name }}</span>
                                <span class="mt-1 line-clamp-3 block text-[0.85rem] leading-snug text-slate">{{ $t->summary ?: $t->menu_text }}</span>
                            </span>
                        </a>
                    @endforeach
                    <a href="{{ route('packages.index') }}" class="flex min-h-11 items-center justify-between bg-amber/20 px-4 text-sm font-semibold uppercase tracking-[0.1em] text-amber-ink">All packages @icon('arrow-right', 'size-4')</a>
                </div></div></div>
            </li>

            <li class="border-b border-ink/10">
                <button type="button" @click="sub = sub === 'about' ? null : 'about'" :aria-expanded="sub === 'about'" aria-controls="m-sub-about" class="flex min-h-12 w-full items-center justify-between text-left text-[0.95rem] font-semibold uppercase tracking-[0.08em] text-ink [&[aria-expanded=true]>svg]:rotate-180">
                    About Us @icon('chevron-down', 'size-5 shrink-0 transition-transform duration-200')
                </button>
                <div id="m-sub-about" class="grid transition-[grid-template-rows] duration-300 ease-out motion-reduce:transition-none" style="grid-template-rows: 0fr" :style="sub === 'about' ? 'grid-template-rows: 1fr' : 'grid-template-rows: 0fr'" :inert="sub !== 'about'" inert><div class="min-h-0 space-y-3 overflow-hidden"><div class="space-y-3 pb-4">
                    @foreach($aboutItems as [$label, $url, $img, $text])
                        <a href="{{ $url }}" class="flex items-center gap-4 border border-amber/40 bg-white p-2.5">
                            <span class="size-[4.5rem] shrink-0 overflow-hidden bg-sky-soft"><x-photo :base="$img" alt="" :widths="[480, 800]" sizes="72px" :ratio="[480, 480]" class="size-full" /></span>
                            <span class="min-w-0">
                                <span class="block font-display text-[1.15rem] leading-tight text-ink">{{ $label }}</span>
                                <span class="mt-1 line-clamp-3 block text-[0.85rem] leading-snug text-slate">{{ $text }}</span>
                            </span>
                        </a>
                    @endforeach
                </div></div></div>
            </li>

            <li class="border-b border-ink/10"><a href="{{ route('contact') }}" class="flex min-h-12 items-center text-[0.95rem] font-semibold uppercase tracking-[0.08em] text-ink">Contact Us</a></li>

            <li class="pt-4"><x-button square variant="cta" class="w-full uppercase tracking-[0.12em]" @click="open = false; $store.modal.open()">Get a free quote</x-button></li>
        </ul>
    </nav>
</header>
