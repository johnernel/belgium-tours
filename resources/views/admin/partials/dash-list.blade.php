{{-- Dashboard card: a short ranked list with a bar behind each count. Include with: title, sub, rows (label => count). --}}
<section class="rounded-2xl bg-paper p-5 shadow-card">
    <h2 class="text-lg">{{ $title }}</h2>
    <p class="text-xs text-slate">{{ $sub }}</p>
    @if(empty($rows))
        <p class="mt-4 text-sm text-slate">Nothing yet.</p>
    @else
        @php $top = max($rows); @endphp
        <ul class="mt-3 space-y-3">
            @foreach($rows as $label => $n)
                <li>
                    <div class="flex items-baseline justify-between gap-3 text-sm"><span class="min-w-0 truncate">{{ $label }}</span><strong>{{ $n }}</strong></div>
                    <div class="mt-1 h-1.5 rounded-full bg-ink/10"><div class="h-1.5 rounded-full bg-amber" style="width: {{ max(4, round($n / $top * 100)) }}%"></div></div>
                </li>
            @endforeach
        </ul>
    @endif
</section>
