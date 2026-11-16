@extends('layouts.admin')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach([['Enquiries this week', $weekCount], ['New, not yet actioned', $newCount], ['All enquiries', $totalCount], ['Published packages', $publishedPackages]] as [$label, $n])
        <div class="rounded-2xl bg-paper p-5 shadow-card"><p class="text-sm text-slate">{{ $label }}</p><p class="mt-1 font-display text-4xl font-semibold">{{ $n }}</p></div>
    @endforeach
</div>

@if(! $ghlOn)<p class="mt-4 rounded-xl bg-amber/30 p-4 text-sm"><strong>GoHighLevel sync is off.</strong> Add GHL_API_TOKEN and GHL_LOCATION_ID to .env to switch it on. Enquiries are still saved here.</p>@endif
@if($ghlFailed)<p class="mt-4 rounded-xl bg-red-50 p-4 text-sm text-red-900"><strong>{{ $ghlFailed }} enquiries failed to sync to GoHighLevel.</strong> Open them and press Retry.</p>@endif

<section class="mt-6 rounded-2xl bg-paper p-5 shadow-card" aria-labelledby="rec">
    <div class="flex items-center justify-between"><h2 id="rec" class="text-xl">Recent enquiries</h2><a href="{{ route('admin.enquiries.index') }}" class="font-semibold underline">See all</a></div>
    @if($recent->isEmpty())
        <p class="mt-3 text-slate">No enquiries yet. They will appear here the moment someone sends one.</p>
    @else
        <ul class="mt-3 divide-y divide-ink/10">
            @foreach($recent as $e)
                <li><a href="{{ route('admin.enquiries.show', $e) }}" class="flex flex-wrap items-center justify-between gap-2 py-3 hover:bg-cream/60">
                    <span><strong>{{ $e->fullName() }}</strong> <span class="text-slate">{{ $e->package_name ?: $e->form_name }}</span></span>
                    <span class="flex items-center gap-3 text-sm text-slate">{{ $e->created_at->diffForHumans() }}
                        <span class="rounded-full bg-sky-soft px-3 py-1 font-semibold text-ink">{{ \App\Models\Enquiry::STATUSES[$e->status] ?? $e->status }}</span></span>
                </a></li>
            @endforeach
        </ul>
    @endif
</section>

<section class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4" aria-label="Quick links">
    @foreach([['admin.packages.create', 'Add a package'], ['admin.quiz.index', 'Edit the quiz'], ['admin.pages.index', 'Edit page text'], ['admin.settings.edit', 'Site settings']] as [$r, $l])
        <a href="{{ route($r) }}" class="rounded-2xl bg-amber p-4 text-center font-sans text-lg font-semibold hover:bg-amber-deep">{{ $l }}</a>
    @endforeach
</section>
@endsection
