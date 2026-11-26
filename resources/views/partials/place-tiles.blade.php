{{-- A grid of photo tiles linking to other destinations or travel styles. Include with: items (list of [name, url, image, alt, focus, sub]), label (aria label). Square corners, photo under a dark gradient, name and a small line bottom-left. --}}
<ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3" aria-label="{{ $label }}">
    @foreach($items as $it)
        <li>
            <a href="{{ $it['url'] }}" class="group relative isolate flex min-h-[15rem] flex-col justify-end overflow-hidden bg-ink p-6 text-white">
                @if(!empty($it['image']))<x-photo :base="$it['image']" :alt="''" sizes="(min-width:1024px) 30vw, (min-width:640px) 46vw, 92vw" fetchpriority="low" :ratio="[1600, 1067]" class="absolute inset-0 -z-20 size-full transition-transform duration-700 group-hover:scale-105" style="object-position: {{ $it['focus'] ?: '50% 50%' }}" />@endif
                <span class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/85 via-ink/25 to-transparent" aria-hidden="true"></span>
                <span class="heading-face font-serif text-2xl leading-tight text-white">{{ $it['name'] }}</span>
                @if(!empty($it['sub']))<span class="mt-1 text-sm text-white/90">{{ $it['sub'] }}</span>@endif
                <span class="mt-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-white">Explore<span class="sr-only"> {{ $it['name'] }}</span> @icon('arrow-right', 'size-3.5 transition-transform group-hover:translate-x-1')</span>
            </a>
        </li>
    @endforeach
</ul>