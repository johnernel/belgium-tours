@props(['name', 'label', 'value' => null])
<div {{ $attributes->only('class') }}>
    <label for="trix-{{ $name }}" class="mb-1 block text-sm font-semibold">{{ $label }}</label>
    <input id="v-{{ $name }}" type="hidden" name="{{ $name }}" value="{{ old($name, $value) }}">
    <trix-editor id="trix-{{ $name }}" input="v-{{ $name }}" class="prose-page !max-w-none"></trix-editor>
</div>
