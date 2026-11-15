@extends('layouts.admin')
@section('title', 'Media')
@section('heading', 'Media library')

@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.media.store') }}" class="mb-6 max-w-2xl space-y-3 rounded-2xl bg-paper p-5 shadow-card">
    @csrf
    <h2 class="text-xl">Upload photos</h2>
    <input type="file" name="files[]" multiple required accept="image/jpeg,image/png,image/webp" class="block text-sm">
    <x-admin.input name="alt" label="Describe the photo (alt text)" :required="true" help="One description is applied to every photo in this upload." />
    <p class="text-xs text-slate">JPG, PNG or WebP, up to 8 MB each. Photos are resized to 480, 800, 1280 and 1600 px wide and converted to WebP. Free-licence or your own photos only.</p>
    <x-button type="submit">Upload</x-button>
</form>

@if($items->isEmpty())
    <p class="rounded-2xl bg-paper p-6 text-center text-slate shadow-card">No uploads yet.</p>
@else
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($items as $m)
            <figure class="space-y-2 rounded-2xl bg-paper p-3 shadow-card">
                <x-photo :base="$m->path" :alt="$m->alt ?? ''" sizes="260px" class="aspect-[4/3] w-full rounded-xl" />
                <figcaption class="text-xs text-slate">{{ $m->alt }}</figcaption>
                <input readonly value="{{ asset($m->path) }}-800.webp" aria-label="Image address" class="w-full rounded-lg border border-slate/30 px-2 py-1 text-xs" onclick="this.select()">
                <form method="POST" action="{{ route('admin.media.destroy', $m) }}" onsubmit="return confirm('Delete this photo?')">@csrf @method('DELETE')<button class="text-sm font-semibold text-red-800 underline">Delete</button></form>
            </figure>
        @endforeach
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
@endif
@endsection
