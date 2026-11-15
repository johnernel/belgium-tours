@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null, 'required' => false])
<div {{ $attributes->only('class') }}>
    <label for="a-{{ $name }}" class="mb-1 block text-sm font-semibold">{{ $label }}@if($required) <span aria-hidden="true">*</span>@endif</label>
    <input id="a-{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" @required($required)
           {{ $attributes->except('class')->merge(['class' => 'block min-h-11 w-full rounded-xl border-2 border-slate/30 bg-white px-3.5 py-2 focus:border-ink focus:outline-none']) }}>
    @if($help)<p class="mt-1 text-xs text-slate">{{ $help }}</p>@endif
</div>
