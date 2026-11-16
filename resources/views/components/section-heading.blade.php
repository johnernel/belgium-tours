@props(['title', 'sub' => null, 'align' => 'left', 'tag' => 'h2', 'tone' => 'dark'])
<div {{ $attributes->merge(['class' => 'max-w-2xl '.($align === 'center' ? 'mx-auto text-center' : '')]) }}>
    <{{ $tag }} class="text-heading {{ $tone === 'light' ? '!text-white' : '' }}">{{ $title }}</{{ $tag }}>
    @if($sub)
        <p class="mt-3 text-lg {{ $tone === 'light' ? 'text-white/85' : 'text-slate' }}">{{ $sub }}</p>
    @endif
</div>
