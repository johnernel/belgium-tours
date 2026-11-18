{{-- One accordion for the package page (Tour Highlights and Day by Day share it so they look the same).
     $items: [['title', 'text', 'img' => ['base','alt']|null, 'points' => []]], $kind: 'highlight' (numbered circle) or 'day' (amber "Day n" pill).
     Each instance has its own Alpine state, so opening a highlight never closes a day. One item open at a time, the first by default. --}}
<div x-data="{ open: 0 }" class="space-y-3">
    @foreach($items as $i => $item)
        @php $img = $item['img'] ?? null; @endphp
        <div class="overflow-hidden rounded-3xl bg-paper shadow-card">
            <h3>
                <button type="button" id="{{ $kind }}-h-{{ $i }}" @click="open = open === {{ $i }} ? null : {{ $i }}"
                        :aria-expanded="open === {{ $i }} ? 'true' : 'false'" aria-controls="{{ $kind }}-p-{{ $i }}"
                        class="flex w-full cursor-pointer items-center gap-4 p-4 text-left sm:p-5 [&[aria-expanded=true]>svg]:rotate-180">
                    @if($kind === 'day')
                        <span class="shrink-0 rounded-full bg-amber px-3.5 py-1.5 text-sm font-bold text-ink">Day {{ $i + 1 }}</span>
                    @else
                        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-amber text-sm font-bold text-ink" aria-hidden="true">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                    @endif
                    <span class="flex-1 font-display text-lg leading-snug text-ink sm:text-xl">{{ $item['title'] }}</span>
                    @icon('chevron-down', 'size-5 shrink-0 text-slate transition-transform duration-300')
                </button>
            </h3>
            <div id="{{ $kind }}-p-{{ $i }}" role="region" aria-labelledby="{{ $kind }}-h-{{ $i }}"
                 class="grid transition-[grid-template-rows] duration-300 ease-out motion-reduce:transition-none"
                 style="grid-template-rows: {{ $i === 0 ? '1fr' : '0fr' }}"
                 :style="open === {{ $i }} ? 'grid-template-rows: 1fr' : 'grid-template-rows: 0fr'"
                 @if($i !== 0) inert @endif :inert="open !== {{ $i }}">
                <div class="min-h-0 overflow-hidden">
                    <div class="grid gap-5 px-4 pb-5 sm:px-5 {{ $img ? 'md:grid-cols-[minmax(0,17rem)_1fr] md:items-start' : '' }}">
                        @if($img)
                            <div class="relative aspect-[4/3] overflow-hidden rounded-2xl bg-sky-soft">
                                <x-photo :base="$img['base']" :alt="$img['alt']" :widths="[480, 800, 1280]" sizes="(min-width:768px) 272px, 92vw" fetchpriority="low" :ratio="[1600, 1200]" class="absolute inset-0 size-full" />
                            </div>
                        @endif
                        <div>
                            <p class="text-base leading-relaxed text-slate sm:text-lg">{{ $item['text'] }}</p>
                            @if(! empty($item['points']))
                                <ul class="mt-3 space-y-1.5 text-slate">
                                    @foreach($item['points'] as $p)
                                        <li class="flex items-start gap-3"><span class="mt-[0.7em] h-0.5 w-3 shrink-0 rounded bg-amber" aria-hidden="true"></span><span>{{ $p }}</span></li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
