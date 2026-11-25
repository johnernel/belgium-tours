@extends('layouts.admin')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
@php
    $statusLabel = \App\Models\Enquiry::STATUSES;
    $first = $chart['days'][0]['date'] ?? now();
    $last = end($chart['days'])['date'] ?? now();
@endphp

{{-- Period filter --}}
<div class="flex justify-end">
    <nav class="inline-flex overflow-hidden rounded-full border border-ink/10 bg-white text-sm font-semibold" aria-label="Period">
        @foreach(\App\Services\DashboardStats::PERIODS as $p)
            <a href="{{ route('admin.dashboard', ['days' => $p]) }}" @if($days === $p) aria-current="true" @endif class="px-4 py-2 {{ $days === $p ? 'bg-ink text-white' : 'text-slate hover:bg-cream' }}">{{ $p }} days</a>
        @endforeach
    </nav>
</div>

{{-- Needs your attention --}}
@if($waiting || $failed['ghl'] || $failed['sheet'])
    <div class="mt-4 rounded-xl border border-amber/40 bg-amber/10 p-4 text-sm" role="status">
        <p class="font-semibold">Needs your attention</p>
        @if($waiting)<p class="mt-1">{{ $waiting }} new {{ \Illuminate\Support\Str::plural('inquiry', $waiting) }} {{ $waiting === 1 ? 'has' : 'have' }} waited more than 24 hours. <a class="font-semibold underline" href="{{ route('admin.enquiries.index', ['status' => 'new']) }}">Open {{ $waiting === 1 ? 'it' : 'them' }}</a></p>@endif
        @if($failed['ghl'])<p class="mt-1">{{ $failed['ghl'] }} {{ \Illuminate\Support\Str::plural('inquiry', $failed['ghl']) }} failed to sync to GoHighLevel. Open {{ $failed['ghl'] === 1 ? 'it' : 'them' }} and press Retry.</p>@endif
        @if($failed['sheet'])<p class="mt-1">{{ $failed['sheet'] }} {{ \Illuminate\Support\Str::plural('inquiry', $failed['sheet']) }} failed to reach the Google Sheet. Open {{ $failed['sheet'] === 1 ? 'it' : 'them' }} and press "Send to sheet".</p>@endif
    </div>
@endif

{{-- Stat cards --}}
<div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="rounded-2xl bg-paper p-5 shadow-card"><p class="text-sm text-slate">Enquiries, last {{ $days }} days</p><p class="mt-1 font-display text-4xl font-semibold">{{ $summary['total'] }}</p><p class="mt-1 text-sm text-slate">+{{ $summary['newInPeriod'] }} new</p></div>
    <div class="rounded-2xl bg-paper p-5 shadow-card"><p class="text-sm text-slate">New, not yet actioned</p><p class="mt-1 font-display text-4xl font-semibold">{{ $summary['newAll'] }}</p><p class="mt-1 text-sm"><a class="font-semibold underline" href="{{ route('admin.enquiries.index', ['status' => 'new']) }}">Open the inbox →</a></p></div>
    <div class="rounded-2xl bg-paper p-5 shadow-card"><p class="text-sm text-slate">From Google Ads</p><p class="mt-1 font-display text-4xl font-semibold">{{ $summary['ads'] }}</p><p class="mt-1 text-sm text-slate">{{ $summary['adsPct'] }} of enquiries</p></div>
    <div class="rounded-2xl bg-paper p-5 shadow-card"><p class="text-sm text-slate">Won</p><p class="mt-1 font-display text-4xl font-semibold">{{ $summary['won'] }}</p><p class="mt-1 text-sm text-slate">{{ $summary['wonPct'] }} of enquiries in this period</p></div>
</div>

{{-- Chart and pipeline --}}
<div class="mt-4 grid gap-4 lg:grid-cols-[1.9fr_1fr]">
    <section class="rounded-2xl bg-paper p-5 shadow-card" aria-labelledby="chart-title">
        <div class="flex items-baseline justify-between gap-3"><h2 id="chart-title" class="text-lg">Enquiries per day</h2><span class="text-sm text-slate">max {{ $chart['max'] }} / day</span></div>
        <div class="mt-4 flex h-44 items-end gap-px border-b border-ink/10" role="img" aria-label="Bar chart of enquiries per day, highest day {{ $chart['max'] }}">
            @foreach($chart['days'] as $d)
                <div class="group relative flex h-full min-w-0 flex-1 items-end" title="{{ $d['date']->format('D j M') }}: {{ $d['n'] }}">
                    <div class="w-full rounded-t-sm {{ $d['n'] ? 'bg-ink' : 'bg-ink/10' }}" style="height: {{ $d['n'] ? max(4, round($d['n'] / $chart['max'] * 100)) : 2 }}%"></div>
                </div>
            @endforeach
        </div>
        <div class="mt-2 flex justify-between text-xs text-slate"><span>{{ $first->format('D j M') }}</span><span>{{ $last->format('D j M') }}</span></div>
    </section>

    <section class="rounded-2xl bg-paper p-5 shadow-card" aria-labelledby="pipe-title">
        <h2 id="pipe-title" class="text-lg">Pipeline</h2>
        <ul class="mt-3 space-y-3">
            @php $pmax = max(1, collect($pipeline)->max('n')); @endphp
            @foreach($pipeline as $s)
                <li>
                    <div class="flex items-baseline justify-between text-sm"><a class="underline" href="{{ route('admin.enquiries.index', ['status' => $s['key']]) }}">{{ $s['label'] }}</a><strong>{{ $s['n'] }}</strong></div>
                    <div class="mt-1 h-1.5 rounded-full bg-ink/10"><div class="h-1.5 rounded-full bg-ink" style="width: {{ $s['n'] ? max(4, round($s['n'] / $pmax * 100)) : 0 }}%"></div></div>
                </li>
            @endforeach
        </ul>
    </section>
</div>

{{-- Four ranked lists --}}
<div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @include('admin.partials.dash-list', ['title' => 'Where they came from', 'sub' => 'Source / medium (latest touch)', 'rows' => $sources])
    @include('admin.partials.dash-list', ['title' => 'Campaigns', 'sub' => 'utm_campaign', 'rows' => $campaigns])
    @include('admin.partials.dash-list', ['title' => 'Forms', 'sub' => 'Which form was used', 'rows' => $forms])
    @include('admin.partials.dash-list', ['title' => 'Popular journeys', 'sub' => 'Enquiries naming a package', 'rows' => $journeys])
</div>

{{-- Recent enquiries and site health --}}
<div class="mt-6 grid gap-4 lg:grid-cols-[1.9fr_1fr]">
    <section aria-labelledby="rec">
        <div class="mb-2 flex items-end justify-between"><h2 id="rec" class="text-xl">Recent enquiries</h2><a href="{{ route('admin.enquiries.index') }}" class="text-sm font-semibold underline">View all</a></div>
        <div class="overflow-x-auto rounded-2xl bg-paper shadow-card">
            @if($recent->isEmpty())
                <p class="p-5 text-slate">No enquiries yet. They will appear here the moment someone sends one.</p>
            @else
                <table class="w-full text-left text-sm">
                    <thead><tr><th class="p-3">When</th><th class="p-3">Name</th><th class="p-3">Form</th><th class="p-3">Source</th><th class="p-3">Status</th></tr></thead>
                    <tbody class="divide-y divide-ink/10">
                        @foreach($recent as $e)
                            <tr>
                                <td class="p-3 whitespace-nowrap">{{ $e->created_at->diffForHumans() }}</td>
                                <td class="p-3"><a class="font-semibold underline" href="{{ route('admin.enquiries.show', $e) }}">{{ $e->fullName() }}</a><br><span class="text-xs text-slate">{{ $e->email }}</span></td>
                                <td class="p-3">{{ $e->package_name ?: (['hero-quiz' => 'Main quiz', 'modal' => 'Quote popup', 'package-quote' => 'Package quote', 'question' => 'Ask a question', 'contact' => 'Contact form'][$e->form_name] ?? $e->form_name) }}</td>
                                <td class="p-3">{{ \App\Services\DashboardStats::sourceOf($e) }}</td>
                                <td class="p-3"><span class="rounded-full bg-sky-soft px-3 py-1 text-xs font-semibold">{{ $statusLabel[$e->status] ?? $e->status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>

    <section aria-labelledby="health">
        <h2 id="health" class="mb-2 text-xl">Site health</h2>
        <ul class="divide-y divide-ink/10 rounded-2xl bg-paper shadow-card">
            @foreach($health as [$ok, $title, $detail, $fix])
                <li class="flex gap-3 p-3 text-sm">
                    <span class="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full text-xs font-bold {{ $ok ? 'bg-green-100 text-green-800' : 'bg-amber/30 text-ink' }}" aria-hidden="true">{{ $ok ? '✓' : '!' }}</span>
                    <span><strong class="font-semibold">{{ $title }}</strong>@unless($ok)<br><span class="text-slate">{{ $detail }}</span> <a class="font-semibold underline" href="{{ route($fix) }}">Fix</a>@endunless<span class="sr-only">{{ $ok ? ': OK' : ': needs attention' }}</span></span>
                </li>
            @endforeach
        </ul>
        {{-- Quick actions --}}
        <div class="mt-4 flex flex-wrap gap-2" aria-label="Quick actions">
            <a href="{{ route('admin.packages.create') }}" class="rounded-full border-2 border-ink px-4 py-2 text-sm font-semibold hover:bg-ink hover:text-white">New package</a>
            <a href="{{ route('admin.posts.create') }}" class="rounded-full border-2 border-ink px-4 py-2 text-sm font-semibold hover:bg-ink hover:text-white">Journal</a>
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="rounded-full border-2 border-ink px-4 py-2 text-sm font-semibold hover:bg-ink hover:text-white">View site</a>
        </div>
    </section>
</div>

<section class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4" aria-label="Quick links">
    @foreach([['admin.packages.create', 'Add a package'], ['admin.quiz.index', 'Edit the quiz'], ['admin.pages.index', 'Edit page text'], ['admin.settings.edit', 'Site settings']] as [$r, $l])
        <a href="{{ route($r) }}" class="rounded-2xl bg-amber p-4 text-center font-sans text-lg font-semibold hover:bg-amber-deep">{{ $l }}</a>
    @endforeach
</section>
@endsection
