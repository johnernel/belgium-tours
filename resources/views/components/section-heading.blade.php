@props(['title', 'sub' => null, 'align' => 'left', 'tag' => 'h2', 'tone' => 'dark'])
<div {{ $attributes->merge(['class' => 'max-w-2xl '.($align === 'center' ? 'mx-auto text-center' : '')]) }}>
    <{{ $tag }} class="text-3xl sm:text-4xl lg:text-[2.75rem] {{ $tone === 'light' ? '!text-white' : '' }}">{{ $title }}</{{ $tag }}>
    @if($sub)
        <p class="mt-3 text-lg {{ $tone === 'light' ? 'text-white/85' : 'text-slate' }}">{{ $sub }}</p>
    @endif
</div>
