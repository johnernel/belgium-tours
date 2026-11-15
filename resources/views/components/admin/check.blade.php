@props(['name', 'label', 'checked' => false])
<div {{ $attributes->only('class') }}>
    <input type="hidden" name="{{ $name }}" value="0">
    <label class="inline-flex min-h-11 cursor-pointer items-center gap-2 font-semibold">
        <input type="checkbox" name="{{ $name }}" value="1" class="size-5 accent-ink" @checked(old($name, $checked))> {{ $label }}
    </label>
</div>
