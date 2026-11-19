@extends('layouts.admin')
@section('title', 'Header & Footer')
@section('heading', 'Header & Footer')

@section('content')
<div class="space-y-5">
    <div class="rounded-2xl border-2 border-amber/60 bg-amber/15 p-4 text-sm" role="note">
        <p class="font-semibold">Careful: this code runs on your live website.</p>
        <p class="mt-1 text-slate">Code added here is placed on your pages exactly as you enter it, for every visitor. Invalid or untrusted code can break pages, slow the site down or put visitors' data at risk. Only paste code from sources you trust (for example your own Facebook Pixel or chat widget), and check the site after saving. If a page breaks, switch the snippet off here.</p>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="max-w-2xl text-slate">Add tracking pixels, scripts, styles or any HTML to the top of the page, right after the opening body tag, or at the very end of the page. Snippets with a lower order number load first.</p>
        <x-button :href="route('admin.snippets.create')" icon="arrow-right">Add code</x-button>
    </div>

    {{-- Wide screens: a table --}}
    <div class="hidden overflow-x-auto rounded-2xl bg-paper shadow-card md:block">
        <table class="w-full text-left">
            <thead class="border-b border-ink/10 text-sm text-slate">
                <tr><th class="p-3">Name</th><th class="p-3">Runs on</th><th class="p-3">Placement</th><th class="p-3">Order</th><th class="p-3">Status</th><th class="p-3 text-right">Actions</th></tr>
            </thead>
            <tbody class="divide-y divide-ink/10">
            @forelse($snippets as $s)
                <tr>
                    <td class="p-3"><span class="font-semibold">{{ $s->name }}</span><span class="block text-xs text-slate">{{ \App\Models\CodeSnippet::TYPES[$s->type] ?? $s->type }}</span></td>
                    <td class="p-3 text-sm">{{ $s->scopeLabel() }}@if($s->scope !== 'all')<span class="block max-w-xs truncate text-xs text-slate" title="{{ implode(', ', (array) $s->pages) }}">{{ implode(', ', (array) $s->pages) }}</span>@endif</td>
                    <td class="p-3 text-sm">{{ ['head' => 'In <head>', 'body_start' => 'Start of <body>', 'body_end' => 'End of <body>'][$s->placement] ?? $s->placement }}</td>
                    <td class="p-3 text-sm">{{ $s->sort }}</td>
                    <td class="p-3">
                        <form method="POST" action="{{ route('admin.snippets.toggle', $s) }}">@csrf
                            <button type="submit" class="inline-flex min-h-9 items-center gap-2 rounded-full px-3 text-sm font-semibold {{ $s->is_active ? 'bg-green-100 text-green-800' : 'bg-ink/10 text-slate' }}" aria-label="{{ $s->is_active ? 'On: click to switch off' : 'Off: click to switch on' }}">
                                <span class="size-2 rounded-full {{ $s->is_active ? 'bg-green-600' : 'bg-slate' }}"></span>{{ $s->is_active ? 'On' : 'Off' }}
                            </button>
                        </form>
                    </td>
                    <td class="whitespace-nowrap p-3 text-right">
                        <a href="{{ route('admin.snippets.edit', $s) }}" class="mr-3 font-semibold underline">Edit</a>
                        <form method="POST" action="{{ route('admin.snippets.destroy', $s) }}" class="inline" onsubmit="return confirm('Delete this code snippet? It will stop running on the site.')">@csrf @method('DELETE')<button class="font-semibold text-red-800 underline">Delete</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-6 text-center text-slate">No code added yet. Use "Add code" to create the first snippet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Phones: one card per snippet --}}
    <div class="space-y-3 md:hidden">
        @forelse($snippets as $s)
            <article class="space-y-3 rounded-2xl bg-paper p-4 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0"><h2 class="break-words text-lg font-semibold">{{ $s->name }}</h2><p class="text-xs text-slate">{{ \App\Models\CodeSnippet::TYPES[$s->type] ?? $s->type }}</p></div>
                    <form method="POST" action="{{ route('admin.snippets.toggle', $s) }}">@csrf
                        <button type="submit" class="inline-flex min-h-9 items-center gap-2 rounded-full px-3 text-sm font-semibold {{ $s->is_active ? 'bg-green-100 text-green-800' : 'bg-ink/10 text-slate' }}" aria-label="{{ $s->is_active ? 'On: tap to switch off' : 'Off: tap to switch on' }}"><span class="size-2 rounded-full {{ $s->is_active ? 'bg-green-600' : 'bg-slate' }}"></span>{{ $s->is_active ? 'On' : 'Off' }}</button>
                    </form>
                </div>
                <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-sm">
                    <dt class="text-slate">Runs on</dt><dd>{{ $s->scopeLabel() }}</dd>
                    <dt class="text-slate">Placement</dt><dd>{{ ['head' => 'In <head>', 'body_start' => 'Start of <body>', 'body_end' => 'End of <body>'][$s->placement] ?? $s->placement }}</dd>
                    <dt class="text-slate">Order</dt><dd>{{ $s->sort }}</dd>
                </dl>
                <div class="flex items-center gap-4 border-t border-ink/10 pt-3">
                    <a href="{{ route('admin.snippets.edit', $s) }}" class="font-semibold underline">Edit</a>
                    <form method="POST" action="{{ route('admin.snippets.destroy', $s) }}" onsubmit="return confirm('Delete this code snippet? It will stop running on the site.')">@csrf @method('DELETE')<button class="font-semibold text-red-800 underline">Delete</button></form>
                </div>
            </article>
        @empty
            <p class="rounded-2xl bg-paper p-6 text-center text-slate shadow-card">No code added yet. Use "Add code" to create the first snippet.</p>
        @endforelse
    </div>
</div>
@endsection