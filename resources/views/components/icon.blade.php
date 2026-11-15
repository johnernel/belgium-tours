@props(['name', 'class' => 'size-6'])
@php
    // Small stroke-icon set (24x24, 2px stroke). Names are what the admin quiz editor stores.
    $paths = \App\Support\Icons::all();

@endphp
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! $paths[$name] ?? $paths['help'] !!}</svg>
