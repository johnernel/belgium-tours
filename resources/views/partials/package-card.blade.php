@props(['package'])
<article class="group relative overflow-hidden rounded-[1.5rem] bg-ink text-white shadow-card">
    <div class="aspect-[4/5] sm:aspect-[3/4]">
        @if($package->hero_image)
            <x-photo :base="$package->hero_image" :alt="$package->hero_image_alt ?? $package->title" sizes="(min-width:1024px) 30vw, (min-width:640px) 45vw, 92vw" class="size-full transition-transform duration-500 group-hover:scale-105" />
        @else
            <div class="size-full bg-slate" role="img" aria-label="Image to be added"></div>
        @endif
    </div>
    <div class="absolute inset-0 bg-gradient-to-t from-ink via-ink/40 to-transparent"></div>
    <div class="absolute inset-x-0 bottom-0 p-5">
        <ul class="mb-3 flex flex-wrap gap-2 text-xs font-bold text-ink">
            <li class="rounded-full bg-amber px-3 py-1">{{ $package->nights }} nights</li>
            @if($package->tourType)<li class="rounded-full bg-sky px-3 py-1">{{ $package->tourType->name }}</li>@endif
        </ul>
        <h3 class="text-xl !text-white sm:text-2xl">
            <a href="{{ route('packages.show', $package->slug) }}" class="after:absolute after:inset-0">{{ $package->title }}</a>
        </h3>
        <p class="mt-2 line-clamp-2 text-sm text-white/85">{{ $package->summary }}</p>
        <p class="mt-3 font-display text-lg font-semibold text-amber">{{ $package->priceLabel() }}</p>
    </div>
</article>
