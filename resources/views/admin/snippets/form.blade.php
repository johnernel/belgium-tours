@extends('layouts.admin')
@section('title', $snippet->exists ? 'Edit code' : 'Add code')
@section('heading', $snippet->exists ? 'Edit: '.$snippet->name : 'Add code')

@section('content')
@php
    $scope = old('scope', $snippet->scope);
    $picked = old('pages_pick', collect((array) $snippet->pages)->filter(fn ($p) => isset($known[$p]))->values()->all());
    $extra = old('pages_extra', collect((array) $snippet->pages)->reject(fn ($p) => isset($known[$p]))->implode("\n"));
@endphp
<form method="POST" class="w-full max-w-none space-y-6" x-data="{ scope: @js($scope) }"
      action="{{ $snippet->exists ? route('admin.snippets.update', $snippet) : route('admin.snippets.store') }}">
    @csrf
    @if($snippet->exists)@method('PUT')@endif

    <div class="rounded-2xl border-2 border-amber/60 bg-amber/15 p-4 text-sm" role="note">
        <p class="font-semibold">Careful: this code runs on your live website.</p>
        <p class="mt-1 text-slate">It is saved and placed on your pages exactly as you type it. Invalid or untrusted code can break pages or put visitors' data at risk. Only paste code from sources you trust, then check the site.</p>
    </div>

    <section class="space-y-5 rounded-2xl bg-paper p-6 shadow-card">
        <div>
            <label for="a-name" class="mb-1 block text-sm font-semibold">Name <span class="text-red-700" aria-hidden="true">*</span></label>
            <input id="a-name" name="name" type="text" required maxlength="120" value="{{ old('name', $snippet->name) }}" class="block min-h-11 w-full rounded-xl border-2 border-slate/30 bg-white px-3.5 py-2 focus:border-ink focus:outline-none">
            <p class="mt-1 text-xs text-slate">Only for you, e.g. Facebook Pixel or Chat widget.</p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <x-admin.select name="type" label="Type" :options="\App\Models\CodeSnippet::TYPES" :value="$snippet->type" />
                <p class="mt-1 text-xs text-slate">Bare code is wrapped in the right tag for you. Code that already starts with a tag (for example &lt;script src="..."&gt;) is used exactly as pasted.</p>
            </div>
            <div>
                <x-admin.select name="placement" label="Place it" :options="\App\Models\CodeSnippet::PLACEMENTS" :value="$snippet->placement" />
                <p class="mt-1 text-xs text-slate">Most tracking and style code goes in the head. Noscript fallbacks go at the start of the body. Widgets can go at the end of the body.</p>
            </div>
        </div>

        <div>
            <label for="a-code" class="mb-1 block text-sm font-semibold">Code <span class="text-red-700" aria-hidden="true">*</span></label>
            <textarea id="a-code" name="code" rows="14" required spellcheck="false" autocomplete="off" autocapitalize="off" placeholder="<script>...</script>"
                      class="block w-full rounded-xl border-2 border-slate/30 bg-white px-3.5 py-2 font-mono text-sm leading-relaxed focus:border-ink focus:outline-none" style="tab-size: 2; white-space: pre; overflow-x: auto">{{ old('code', $snippet->code) }}</textarea>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="a-scope" class="mb-1 block text-sm font-semibold">Runs on</label>
                <select id="a-scope" name="scope" x-model="scope" class="block min-h-11 w-full rounded-xl border-2 border-slate/30 bg-white px-3 focus:border-ink focus:outline-none">
                    @foreach(\App\Models\CodeSnippet::SCOPES as $k => $v)<option value="{{ $k }}" @selected($scope === $k)>{{ $v }}</option>@endforeach
                </select>
            </div>
            <x-admin.input name="sort" type="number" label="Order" :value="old('sort', $snippet->sort)" help="Lower numbers are output first." />
        </div>

        <fieldset class="space-y-4 rounded-xl border border-ink/10 bg-cream/40 p-4" x-show="scope !== 'all'" @if($scope === 'all') x-cloak @endif>
            <legend class="px-1 text-sm font-semibold"><span x-text="scope === 'except' ? 'Skip the code on these pages' : 'Run the code only on these pages'">Pages</span></legend>
            <div class="grid gap-x-6 gap-y-1 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($known as $path => $label)
                    <label class="inline-flex min-h-10 items-center gap-2 text-sm"><input type="checkbox" name="pages_pick[]" value="{{ $path }}" class="size-5 accent-ink" @checked(in_array($path, $picked))> <span>{{ $label }} <code class="text-xs text-slate">{{ $path }}</code></span></label>
                @endforeach
            </div>
            <div>
                <label for="a-pages_extra" class="mb-1 block text-sm font-semibold">Other pages (one per line)</label>
                <textarea id="a-pages_extra" name="pages_extra" rows="3" spellcheck="false" placeholder="/packages/bruges-ghent-medieval-escape&#10;/tours/*&#10;https://yoursite.com/contact" class="block w-full rounded-xl border-2 border-slate/30 bg-white px-3.5 py-2 font-mono text-sm focus:border-ink focus:outline-none">{{ $extra }}</textarea>
                <p class="mt-1 text-xs text-slate">Page addresses, slugs or full URLs. Use * as a wildcard, e.g. /packages/* means every package page.</p>
                @error('pages_extra')<p class="mt-1 text-sm font-semibold text-red-800">{{ $message }}</p>@enderror
            </div>
        </fieldset>

        <x-admin.check name="is_active" label="Active (output on the site)" :checked="old('is_active', $snippet->exists ? $snippet->is_active : true)" />
    </section>

    <div class="flex flex-wrap items-center gap-3">
        <x-button type="submit">Save</x-button>
        <a href="{{ route('admin.snippets.index') }}" class="inline-flex min-h-11 items-center rounded-full border-2 border-slate/30 px-5 font-semibold">Cancel</a>
    </div>
</form>
@if($snippet->exists)
    <form method="POST" action="{{ route('admin.snippets.destroy', $snippet) }}" class="mt-4" onsubmit="return confirm('Delete this code snippet? It will stop running on the site.')">@csrf @method('DELETE')<x-button type="submit" variant="outline" size="sm">Delete this snippet</x-button></form>
@endif
@endsection