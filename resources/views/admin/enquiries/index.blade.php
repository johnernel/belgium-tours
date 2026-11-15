@extends('layouts.admin')
@section('title', 'Enquiries')
@section('heading', 'Enquiries')

@section('content')
<form method="GET" class="mb-4 grid gap-3 rounded-2xl bg-paper p-4 shadow-card sm:grid-cols-2 lg:grid-cols-6" aria-label="Filter enquiries">
    <x-admin.input name="q" label="Search" :value="request('q')" class="lg:col-span-2" placeholder="Name, email, phone, package" />
    <x-admin.select name="status" label="Status" :options="\App\Models\Enquiry::STATUSES" :value="request('status')" blank="Any status" />
    <x-admin.select name="source" label="Source" :options="$sources->mapWithKeys(fn ($s) => [$s => $s])->all()" :value="request('source')" blank="Any source" />
    <x-admin.input name="from" type="date" label="From" :value="request('from')" />
    <x-admin.input name="to" type="date" label="To" :value="request('to')" />
    <div class="flex gap-2 lg:col-span-6">
        <x-button type="submit" variant="dark" size="sm">Filter</x-button>
        <x-button :href="route('admin.enquiries.index')" variant="outline" size="sm">Clear</x-button>
        <x-button :href="route('admin.enquiries.export', request()->query())" variant="sky" size="sm" class="ml-auto">Export CSV</x-button>
    </div>
</form>

<div class="overflow-x-auto rounded-2xl bg-paper shadow-card">
    <table class="w-full min-w-[44rem] text-left">
        <thead class="border-b border-ink/10 text-sm text-slate"><tr><th class="p-3">Received</th><th class="p-3">Name</th><th class="p-3">Interest</th><th class="p-3">Source</th><th class="p-3">Status</th><th class="p-3">GHL</th></tr></thead>
        <tbody class="divide-y divide-ink/10">
        @forelse($enquiries as $e)
            <tr class="hover:bg-cream/60">
                <td class="p-3 whitespace-nowrap text-sm">{{ $e->created_at->format('d M Y H:i') }}</td>
                <td class="p-3"><a class="font-semibold underline" href="{{ route('admin.enquiries.show', $e) }}">{{ $e->fullName() }}</a><div class="text-sm text-slate">{{ $e->email }}</div></td>
                <td class="p-3 text-sm">{{ $e->package_name ?: ($e->tour_type ?: $e->form_name) }}</td>
                <td class="p-3 text-sm">{{ $e->tracking_last['utm_source'] ?? 'direct' }}</td>
                <td class="p-3"><span class="rounded-full bg-sky-soft px-3 py-1 text-sm font-semibold">{{ \App\Models\Enquiry::STATUSES[$e->status] ?? $e->status }}</span></td>
                <td class="p-3 text-sm">{{ $e->ghl_status }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="p-6 text-center text-slate">No enquiries match. New ones show up here as soon as they arrive.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $enquiries->links() }}</div>
@endsection
