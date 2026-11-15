@props(['name', 'label', 'options' => [], 'value' => null, 'blank' => null])
<div {{ $attributes->only('class') }}>
    <label for="a-{{ $name }}" class="mb-1 block text-sm font-semibold">{{ $label }}</label>
    <select id="a-{{ $name }}" name="{{ $name }}" class="block min-h-11 w-full rounded-xl border-2 border-slate/30 bg-white px-3 focus:border-ink focus:outline-none">
        @if($blank !== null)<option value="">{{ $blank }}</option>@endif
        @foreach($options as $k => $v)<option value="{{ $k }}" @selected((string) old($name, $value) === (string) $k)>{{ $v }}</option>@endforeach
    </select>
</div>
