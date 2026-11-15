@props(['question'])
<details {{ $attributes->merge(['class' => 'group rounded-2xl bg-paper shadow-card']) }}>
    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 rounded-2xl px-5 py-4 font-display text-lg font-medium sm:px-6 [&::-webkit-details-marker]:hidden">
        <span>{{ $question }}</span>
        @icon('chevron-down', 'size-5 shrink-0 text-slate transition-transform group-open:rotate-180')
    </summary>
    <div class="px-5 pb-5 text-slate sm:px-6">{{ $slot }}</div>
</details>
