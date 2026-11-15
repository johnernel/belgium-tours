@props(['name', 'label', 'type' => 'text', 'required' => false, 'hint' => null, 'autocomplete' => null])
{{-- Text-style field with a visible label and an inline error slot (Alpine "errors"). --}}
<div {{ $attributes->only('class') }}>
    <label for="{{ $attributes->get('id', $name) }}" class="mb-1.5 block text-sm font-semibold">
        {{ $label }}@if($required)<span class="text-ink" aria-hidden="true"> *</span>@endif
    </label>
    <input id="{{ $attributes->get('id', $name) }}" name="{{ $name }}" type="{{ $type }}"
           @if($required) required @endif
           @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
           value="{{ old($name) }}"
           x-bind:aria-invalid="fieldError('{{ $name }}') ? 'true' : 'false'"
           {{ $attributes->except(['class', 'id'])->merge(['class' => 'block min-h-11 w-full rounded-xl border-2 border-slate/30 bg-white px-3.5 py-2.5 text-base placeholder:text-slate/60 focus:border-ink focus:outline-none']) }}>
    @if($hint)<p class="mt-1 text-xs text-slate">{{ $hint }}</p>@endif
    <p class="mt-1 text-sm font-semibold text-red-800" x-show="fieldError('{{ $name }}')" x-text="fieldError('{{ $name }}')" role="alert"></p>
    @error($name)<p class="mt-1 text-sm font-semibold text-red-800" x-show="!fieldError('{{ $name }}')">{{ $message }}</p>@enderror
</div>
