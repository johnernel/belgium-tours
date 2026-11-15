@extends('layouts.admin')
@section('title', 'Admin users')
@section('heading', 'Admin users')

@section('content')
<div class="mb-4 flex justify-end"><x-button :href="route('admin.users.create')" icon="arrow-right">Add admin</x-button></div>
<div class="overflow-x-auto rounded-2xl bg-paper shadow-card">
    <table class="w-full min-w-[28rem] text-left">
        <thead class="border-b border-ink/10 text-sm text-slate"><tr><th class="p-3">Name</th><th class="p-3">Email</th><th class="p-3 text-right">Actions</th></tr></thead>
        <tbody class="divide-y divide-ink/10">
        @foreach($users as $u)
            <tr><td class="p-3 font-semibold">{{ $u->name }}@if($u->is(auth()->user())) <span class="text-sm font-normal text-slate">(you)</span>@endif</td><td class="p-3">{{ $u->email }}</td>
                <td class="p-3 text-right whitespace-nowrap"><a class="mr-3 font-semibold underline" href="{{ route('admin.users.edit', $u) }}">Edit</a>
                    @unless($u->is(auth()->user()))<form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="inline" onsubmit="return confirm('Remove this admin?')">@csrf @method('DELETE')<button class="font-semibold text-red-800 underline">Remove</button></form>@endunless</td></tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
