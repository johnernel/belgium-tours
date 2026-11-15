@props(['name', 'label', 'value' => null, 'help' => null, 'rows' => 4])
<div {{ $attributes->only('class') }}>
    <label for="a-{{ $name }}" class="mb-1 block text-sm font-semibold">{{ $label }}</label>
    <textarea id="a-{{ $name }}" name="{{ $name }}" rows="{{ $rows }}"
              class="block w-full rounded-xl border-2 border-slate/30 bg-white px-3.5 py-2 focus:border-ink focus:outline-none">{{ old($name, $value) }}</textarea>
    @if($help)<p class="mt-1 text-xs text-slate">{{ $help }}</p>@endif
</div>
