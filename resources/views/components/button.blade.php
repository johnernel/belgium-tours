@props(['href' => null, 'variant' => 'primary', 'size' => 'md', 'icon' => null, 'square' => false])
@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-full font-sans font-semibold leading-none transition-colors duration-150 min-h-11 cursor-pointer';
    $sizes = ['sm' => 'px-4 py-2.5 text-sm', 'md' => 'px-6 py-3.5 text-base', 'lg' => 'px-8 py-4 text-lg'];
    $variants = [
        'primary' => 'bg-amber text-ink hover:bg-amber-deep',
        // Brand CTA: solid amber, white text, light blue on hover (hover text goes dark so it stays readable).
        'cta' => 'bg-amber text-white hover:bg-sky-hover hover:text-ink',
        'dark' => 'bg-ink text-cream hover:bg-slate',
        'sky' => 'bg-sky text-ink hover:bg-sky-soft',
        'outline' => 'border-2 border-ink text-ink hover:bg-ink hover:text-cream',
        'light' => 'border-2 border-white/80 text-white hover:bg-white hover:text-ink',
    ];
    $cls = "$base {$sizes[$size]} {$variants[$variant]}";
    if ($square) { $cls = str_replace('rounded-full', 'rounded-none', $cls); }
@endphp
@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $cls]) }}>{{ $slot }}@if($icon)@icon($icon, 'size-5')@endif</a>
@else
    <button type="{{ $attributes->get('type', 'button') }}" {{ $attributes->except('type')->merge(['class' => $cls]) }}>{{ $slot }}@if($icon)@icon($icon, 'size-5')@endif</button>
@endif
