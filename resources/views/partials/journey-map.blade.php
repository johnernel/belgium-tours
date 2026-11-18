{{-- "The Journey Never Ends": text on the left, an interactive map of Belgium on the right (Alpine component `journeyMap` in
     app.js, styles `.jm-*` in app.css). Data comes from App\Support\JourneyMap. Copy keys journey_* in Admin > Pages > Home. --}}
@php
    $jm = \App\Support\JourneyMap::data();
    [$vw, $vh] = $jm['viewBox'];
@endphp
<section class="jm on-dark relative isolate overflow-hidden py-16 text-cream sm:py-24" aria-labelledby="journey-title"
         x-data="journeyMap({{ \Illuminate\Support\Js::from($jm['cities']) }})" @keydown.escape.window="close(true)" @resize.window.debounce.150ms="open && place()">
    <div class="container-page grid items-center gap-12 lg:grid-cols-[0.78fr_1.22fr] lg:gap-10">
        {{-- Left: the words --}}
        <div class="relative z-10">
            <h2 id="journey-title" class="text-heading font-light">
                <em class="heading-accent block">{{ $c('journey_title_1') ?: 'The Journey' }}</em>
                <span class="block text-cream">{{ $c('journey_title_2') ?: 'Never Ends' }}</span>
            </h2>
            <p class="mt-6 max-w-md text-lg leading-relaxed text-cream/80">{{ $c('journey_text') ?: 'From hidden canals to medieval towns, every journey adds a new chapter to your story.' }}</p>
            <a href="{{ route('destinations.index') }}" class="mt-8 inline-flex min-h-12 items-center gap-2 rounded-full border-2 border-amber px-7 py-3 font-sans text-[0.8rem] font-semibold uppercase tracking-[0.16em] text-amber transition-colors duration-200 hover:bg-amber hover:text-ink focus-visible:bg-amber focus-visible:text-ink">
                {{ $c('journey_cta') ?: 'Plan my journey now' }} @icon('arrow-right', 'size-4')
            </a>
        </div>

        {{-- Right: the map --}}
        <div class="relative z-10 min-w-0">
            <div class="jm-wrap relative mx-auto w-full max-w-[44rem]" x-ref="wrap">
            <div class="jm-stage relative w-full" x-ref="stage" style="aspect-ratio: {{ $vw }} / {{ $vh }}"
                 @mousemove="parallax($event)" @mouseleave="resetParallax()" role="group" aria-label="Map of Belgium with ten cities. Select a city for photos and tours.">
                <svg class="absolute inset-0 size-full" viewBox="0 0 {{ $vw }} {{ $vh }}" fill="none" aria-hidden="true" focusable="false">
                    <defs>
                        <filter id="jm-grain" x="0" y="0" width="100%" height="100%">
                            <feTurbulence type="fractalNoise" baseFrequency="0.85" numOctaves="2" seed="7" result="n" />
                            <feColorMatrix in="n" type="matrix" values="0 0 0 0 0.96  0 0 0 0 0.86  0 0 0 0 0.7  0 0 0 0.6 -0.2" result="g" />
                            <feComposite in="g" in2="SourceAlpha" operator="in" />
                        </filter>
                        @foreach($jm['routes'] as $r)
                            <mask id="jm-m{{ $r['i'] }}" maskUnits="userSpaceOnUse" x="0" y="0" width="{{ $vw }}" height="{{ $vh }}">
                                <path d="{{ $r['d'] }}" stroke="#fff" stroke-width="16" fill="none" pathLength="1" class="jm-reveal" style="--i: {{ $r['i'] }}" />
                            </mask>
                        @endforeach
                    </defs>

                    {{-- Belgium: a soft outer outline, then the provinces in dark brown with a faint grain --}}
                    <g class="jm-land">
                        <g stroke="rgb(252 244 223 / 0.2)" stroke-width="3.2" stroke-linejoin="round">@foreach($jm['provinces'] as $p)<path d="{{ $p['d'] }}" />@endforeach</g>
                        <g fill="#2a2320" stroke="rgb(252 244 223 / 0.17)" stroke-width="1.1" stroke-linejoin="round">@foreach($jm['provinces'] as $p)<path d="{{ $p['d'] }}" />@endforeach</g>
                        <g filter="url(#jm-grain)" fill="#fff" opacity="0.28">@foreach($jm['provinces'] as $p)<path d="{{ $p['d'] }}" />@endforeach</g>
                    </g>

                    {{-- Dotted amber route legs; they draw in when the section scrolls into view and brighten with their cities --}}
                    @foreach($jm['routes'] as $r)
                        <path id="jm-r{{ $r['i'] }}" d="{{ $r['d'] }}" mask="url(#jm-m{{ $r['i'] }})" class="jm-route" :class="{ 'is-lit': lit('{{ $r['from'] }}', '{{ $r['to'] }}') }" stroke-width="3.4" stroke-linecap="round" stroke-dasharray="0.1 9.5" />
                    @endforeach

                    {{-- Small planes flying the legs in a loop, turned to follow the path --}}
                    @foreach($jm['planes'] as $pl)
                        <g class="jm-plane" aria-hidden="true">
                            <path d="M7 0 2 -1.9-3.4 -6.6-5 -6.6-3.2 -1.4-6.8-1-8.2-3-9.6-3-8.6 0-9.6 3-8.2 3-6.8 1-3.2 1.4-5 6.6-3.4 6.6 2 1.9Z" transform="scale(1.15)" fill="#fcb345" />
                            <animateMotion dur="{{ $pl['dur'] }}s" begin="{{ $pl['begin'] }}s" repeatCount="indefinite" rotate="auto"><mpath href="#jm-r{{ $pl['route'] }}" /></animateMotion>
                        </g>
                    @endforeach

                    {{-- Faint tethers from a city to its polaroid --}}
                    @foreach($jm['cities'] as $c2)
                        @if($c2['polaroid'])
                            <line x1="{{ $c2['px'] }}" y1="{{ $c2['py'] }}" x2="{{ round($c2['polaroid']['x'] / 100 * $vw, 1) }}" y2="{{ round($c2['polaroid']['y'] / 100 * $vh, 1) }}" stroke="rgb(252 244 223 / 0.28)" stroke-width="1.3" stroke-dasharray="2 5" />
                        @endif
                    @endforeach
                </svg>

                {{-- Photo polaroids: crossfading photos, staggered, paused on hover --}}
                @foreach($jm['cities'] as $i => $city)
                    @if($city['polaroid'])
                        @php $pol = $city['polaroid']; @endphp
                        <div class="jm-pol" :class="{ 'is-hot': hover === '{{ $city['slug'] }}' || open === '{{ $city['slug'] }}' }"
                             style="left: {{ $pol['x'] }}%; top: {{ $pol['y'] }}%; --r0: {{ $pol['rot'] }}deg; --d: {{ $pol['depth'] }}">
                            <button type="button" class="jm-pol-btn" @click.stop="toggle('{{ $city['slug'] }}', $event)" @mouseenter="hover = '{{ $city['slug'] }}'" @mouseleave="hover = null" @focus="hover = '{{ $city['slug'] }}'" @blur="hover = null"
                                    aria-label="{{ $city['name'] }}: photos and {{ $city['countText'] }}" :aria-expanded="open === '{{ $city['slug'] }}'">
                                <span class="jm-photo">
                                    @foreach($city['images'] as $k => $img)
                                        <img src="{{ $img['src'] }}" srcset="{{ $img['srcset'] }}" sizes="(min-width:1024px) 150px, 90px" alt="{{ $img['alt'] }}" width="400" height="300" draggable="false"
                                             @if($k > 0) loading="lazy" @endif decoding="async" class="jm-img" :class="{ 'is-on': idx['{{ $city['slug'] }}'] === {{ $k }} }" @if($k === 0) data-first @endif>
                                    @endforeach
                                </span>
                                <span class="jm-pdots" aria-hidden="true">@foreach($city['images'] as $k => $img)<i :class="{ 'is-on': idx['{{ $city['slug'] }}'] === {{ $k }} }"></i>@endforeach</span>
                            </button>
                            <span class="jm-label">{{ $city['name'] }}</span>
                        </div>
                    @endif
                @endforeach

                {{-- City dots --}}
                @foreach($jm['cities'] as $i => $city)
                    <button type="button" class="jm-dot" :class="{ 'is-hot': hover === '{{ $city['slug'] }}', 'is-open': open === '{{ $city['slug'] }}' }" style="left: {{ $city['x'] }}%; top: {{ $city['y'] }}%; --i: {{ $i }}"
                            @click.stop="toggle('{{ $city['slug'] }}', $event)" @mouseenter="hover = '{{ $city['slug'] }}'" @mouseleave="hover = null" @focus="hover = '{{ $city['slug'] }}'" @blur="hover = null"
                            aria-label="{{ $city['name'] }}, {{ $city['countText'] }}" :aria-expanded="open === '{{ $city['slug'] }}'" aria-controls="jm-card">
                        <span class="jm-core"></span>
                        @unless($city['polaroid'])<span class="jm-tag {{ in_array($city['slug'], ['ypres', 'liege', 'mechelen'], true) ? 'jm-tag-l' : '' }}">{{ $city['name'] }}</span>@endunless
                    </button>
                @endforeach

            </div>

            {{-- Detail card: opens on click, with the city's photos as a small slider --}}
                <div id="jm-card" class="jm-card" x-cloak x-show="cur" x-transition.opacity.duration.200ms tabindex="-1" role="dialog" aria-label="City details" @click.outside="close()"
                     :style="pos">
                    <template x-if="cur">
                        <div>
                            <div class="jm-slide">
                                <template x-for="(im, k) in cur.images" :key="cur.slug + k">
                                    <img :src="im.src" :srcset="im.srcset" sizes="260px" :alt="im.alt" loading="lazy" draggable="false" class="jm-img" :class="{ 'is-on': card === k }">
                                </template>
                                <template x-if="cur.images.length > 1">
                                    <div>
                                        <button type="button" class="jm-nav jm-prev" @click="step(-1)" aria-label="Previous photo">&#8249;</button>
                                        <button type="button" class="jm-nav jm-next" @click="step(1)" aria-label="Next photo">&#8250;</button>
                                        <span class="jm-cdots"><template x-for="(im, k) in cur.images" :key="k"><i :class="{ 'is-on': card === k }"></i></template></span>
                                    </div>
                                </template>
                            </div>
                            <div class="p-4">
                                <h3 class="font-serif text-2xl font-normal leading-tight text-cream" x-text="cur.name"></h3>
                                <p class="mt-1 text-sm text-cream/80" x-text="cur.countText"></p>
                                <template x-if="cur.count > 0">
                                    <a :href="cur.url" class="mt-3 inline-flex min-h-10 items-center gap-2 text-sm font-semibold text-amber underline decoration-amber decoration-2 underline-offset-4 hover:text-white">View tours <span aria-hidden="true">&rarr;</span><span class="sr-only" x-text="' in ' + cur.name"></span></a>
                                </template>
                                <template x-if="cur.count === 0">
                                    <button type="button" class="mt-3 inline-flex min-h-10 items-center gap-2 text-sm font-semibold text-amber underline decoration-amber decoration-2 underline-offset-4 hover:text-white" @click="close(); $store.modal.open()">Plan a trip here <span aria-hidden="true">&rarr;</span></button>
                                </template>
                            </div>
                            <button type="button" class="jm-close" @click="close(true)" aria-label="Close city details">&times;</button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Same links for visitors who cannot use the map (screen readers, no JavaScript) --}}
            <ul class="sr-only">
                @foreach($jm['cities'] as $city)
                    <li><a href="{{ $city['url'] }}">{{ $city['name'] }}: {{ $city['countText'] }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
