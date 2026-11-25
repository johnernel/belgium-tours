@props([
    'title',
    'sub' => null,
    'image',                 // public path of a photo, without the -width.webp suffix
    'alt' => '',
    'crumb' => null,         // optional breadcrumb label shown above the heading
])
{{-- Photo hero for inner pages. Slides under the transparent header (pages that use it set @section('hero')). --}}
<section class="relative isolate -mt-[4.5rem] flex min-h-[26rem] items-end overflow-hidden bg-ink text-white lg:-mt-[8rem] lg:min-h-[33.5rem]">
    <x-photo :base="$image" :alt="$alt" sizes="100vw" :eager="true" class="absolute inset-0 z-0 size-full" />
    <div class="absolute inset-0 z-[1] bg-gradient-to-b from-ink/55 via-ink/40 to-ink/85"></div>
    <div class="absolute inset-0 z-[1] bg-gradient-to-r from-ink/70 via-ink/25 to-transparent"></div>
    <div class="container-page relative z-10 pb-12 pt-32 sm:pb-14">
        @if($crumb)<p class="hero-rise mb-3 text-sm font-semibold text-white/85"><a href="{{ route('home') }}" class="underline decoration-amber decoration-2 underline-offset-4">Home</a> / {{ $crumb }}</p>@endif
        <h1 class="on-dark hero-rise max-w-3xl font-serif text-[2.65rem] font-medium leading-[1.02] !text-white sm:text-6xl lg:text-[4.5rem]">@heading($title)</h1>
        @if($sub)<p class="hero-rise hero-rise-2 mt-6 max-w-xl text-lg text-white/90 sm:text-xl">{{ $sub }}</p>@endif
    </div>
</section>
