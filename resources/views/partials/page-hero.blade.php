@props([
    'title',
    'sub' => null,
    'image',                 // public path of a photo, without the -width.webp suffix
    'alt' => '',
    'crumb' => null,         // optional breadcrumb label shown above the heading
])
{{-- Photo hero for inner pages WITHOUT the quiz (Contact). Built like the home page hero: same height, container, spacing, overlay and headline styling
     (partials/hero-headline), a single photo with the slow zoom, and no "Authentic Trips, Belgian Heart" badge (home only). Pages that use it set @section('hero'). --}}
<section class="relative isolate -mt-[4.5rem] flex min-h-[44rem] flex-col overflow-hidden bg-ink text-white lg:-mt-[8rem] lg:min-h-[50rem]">
    <div class="absolute inset-0 z-0 overflow-hidden bg-ink" aria-hidden="true">
        <x-photo :base="$image" :alt="$alt" sizes="(max-width: 1023px) 110vw, 100vw" :eager="true" class="hero-single absolute inset-0 size-full" />
    </div>
    <div class="absolute inset-0 z-[1] bg-gradient-to-b from-ink/33 to-ink/26"></div>

    <div class="container-wide relative z-10 flex w-full flex-1 items-end pb-12 pt-[calc(4.5rem+2.5rem)] lg:items-center lg:pb-14 lg:pt-[calc(8rem+1.5rem)]">
        <div class="lg:pt-24 max-sm:text-center">
            @if($crumb)<p class="hero-rise text-sm font-semibold text-white [text-shadow:0_1px_8px_rgb(0_0_0/0.5)]"><a href="{{ route('home') }}" class="underline decoration-amber decoration-2 underline-offset-4">Home</a> / {{ $crumb }}</p>@endif
            @include('partials.hero-headline', ['title' => $title])
            @if($sub)<p class="hero-rise hero-rise-2 mt-9 max-w-xl text-lg text-white [text-shadow:0_2px_4px_rgb(0_0_0/0.45),0_4px_24px_rgb(0_0_0/0.5)] max-sm:hidden sm:text-xl">{{ $sub }}</p>@endif
        </div>
    </div>
</section>
