@extends('layouts.admin')
@section('title', 'Packages')
@section('heading', 'Packages')

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <p class="max-w-2xl text-slate">Smaller order numbers show first. Featured packages appear on the home page.</p>
    <x-button :href="route('admin.packages.create')" icon="arrow-right">Add package</x-button>
</div>
<form method="POST" action="{{ route('admin.packages.reorder') }}">
    @csrf
    <div class="overflow-x-auto rounded-2xl bg-paper shadow-card">
        <table class="w-full min-w-[48rem] text-left">
            <thead class="border-b border-ink/10 text-sm text-slate"><tr><th class="p-3">Order</th><th class="p-3">Title</th><th class="p-3">Type</th><th class="p-3">Nights</th><th class="p-3">Status</th><th class="p-3 text-right">Actions</th></tr></thead>
            <tbody class="divide-y divide-ink/10">
            @forelse($packages as $p)
                <tr>
                    <td class="p-3"><input type="number" name="sort[{{ $p->id }}]" value="{{ $p->sort }}" aria-label="Order for {{ $p->title }}" class="w-16 rounded-lg border-2 border-slate/30 px-2 py-1"></td>
                    <td class="p-3"><a class="font-semibold underline" href="{{ route('admin.packages.edit', $p) }}">{{ $p->title }}</a>@if($p->is_featured) <span class="ml-1 rounded-full bg-amber px-2 py-0.5 text-xs font-bold">Featured</span>@endif</td>
                    <td class="p-3 text-sm">{{ $p->tourType?->name }}</td>
                    <td class="p-3 text-sm">{{ $p->nights }}</td>
                    <td class="p-3 text-sm font-semibold">{{ $p->is_published ? 'Published' : 'Draft' }}</td>
                    <td class="p-3 text-right text-sm whitespace-nowrap">
                        <button type="submit" formaction="{{ route('admin.packages.toggle', [$p, 'is_published']) }}" class="mr-2 font-semibold underline">{{ $p->is_published ? 'Unpublish' : 'Publish' }}</button>
                        <button type="submit" formaction="{{ route('admin.packages.toggle', [$p, 'is_featured']) }}" class="mr-2 font-semibold underline">{{ $p->is_featured ? 'Unfeature' : 'Feature' }}</button>
                        <button type="submit" formaction="{{ route('admin.packages.duplicate', $p) }}" class="mr-2 font-semibold underline">Duplicate</button>
                        <a href="{{ route('admin.packages.edit', $p) }}" class="font-semibold underline">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-6 text-center text-slate">No packages yet. Add your first one.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($packages->isNotEmpty())<x-button type="submit" variant="dark" size="sm" class="mt-3">Save order</x-button>@endif
</form>
@endsection
