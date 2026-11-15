@extends('layouts.admin')
@section('title', $cfg['title'])
@section('heading', $cfg['title'])

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <p class="max-w-2xl text-slate">{{ $cfg['help'] ?? '' }}</p>
    <x-button :href="route($routeBase.'.create')" icon="arrow-right">Add {{ strtolower($cfg['singular']) }}</x-button>
</div>
<div class="overflow-x-auto rounded-2xl bg-paper shadow-card">
    <table class="w-full min-w-[32rem] text-left">
        <thead class="border-b border-ink/10 text-sm text-slate"><tr>@foreach($cfg['columns'] as $label => $_)<th class="p-3">{{ $label }}</th>@endforeach<th class="p-3 text-right">Actions</th></tr></thead>
        <tbody class="divide-y divide-ink/10">
        @forelse($rows as $row)
            <tr>
                @foreach($cfg['columns'] as $label => $col)
                    <td class="p-3">{{ is_callable($col) ? $col($row) : $row->{$col} }}</td>
                @endforeach
                <td class="p-3 text-right whitespace-nowrap">
                    <a href="{{ route($routeBase.'.edit', $row) }}" class="mr-3 font-semibold underline">Edit</a>
                    <form method="POST" action="{{ route($routeBase.'.destroy', $row) }}" class="inline" onsubmit="return confirm('Delete this {{ strtolower($cfg['singular']) }}?')">@csrf @method('DELETE')<button class="font-semibold text-red-800 underline">Delete</button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="{{ count($cfg['columns']) + 1 }}" class="p-6 text-center text-slate">Nothing here yet. Use "Add {{ strtolower($cfg['singular']) }}" to create the first one.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
