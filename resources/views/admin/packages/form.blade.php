@extends('layouts.admin')
@section('title', $package->exists ? 'Edit package' : 'Add package')
@section('heading', $package->exists ? 'Edit: '.$package->title : 'Add package')

@section('content')
@php
    $hl = implode("\n", $package->highlights ?? []);
    $inc = implode("\n", $package->inclusions ?? []);
    $exc = implode("\n", $package->exclusions ?? []);
@endphp
<form method="POST" enctype="multipart/form-data" class="max-w-4xl space-y-6"
      action="{{ $package->exists ? route('admin.packages.update', $package) : route('admin.packages.store') }}">
    @csrf
    @if($package->exists)@method('PUT')@endif

    <section class="space-y-4 rounded-2xl bg-paper p-6 shadow-card">
        <h2 class="text-xl">Basics</h2>
        <x-admin.input name="title" label="Title" :value="$package->title" :required="true" />
        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.input name="slug" label="URL name" :value="$package->slug" help="Leave blank to create it from the title." />
            <x-admin.input name="sort" type="number" label="Order (smaller shows first)" :value="$package->sort" />
            <x-admin.select name="tour_type_id" label="Tour type" :options="$tourTypes" :value="$package->tour_type_id" blank="None" />
            <x-admin.select name="destination_id" label="Region" :options="$destinations" :value="$package->destination_id" blank="None" />
            <x-admin.input name="nights" type="number" label="Nights" :value="$package->nights" :required="true" />
            <div class="grid grid-cols-[1fr_6rem] gap-3">
                <x-admin.input name="price_from" type="number" step="0.01" label="From price (per person)" :value="$package->price_from" help="Leave blank to show Price on request." />
                <x-admin.input name="original_price" type="number" step="0.01" label="Original price (crossed out, optional)" :value="$package->original_price" help="Shows From and this price crossed out beside the current price on the package cards. Leave blank for no strikethrough. Must be higher than the From price." />
                <x-admin.select name="currency" label="Currency" :options="['EUR' => 'EUR', 'USD' => 'USD', 'GBP' => 'GBP']" :value="$package->currency" />
            </div>
        </div>
        <div>
            <p class="mb-1 text-sm font-semibold">Destinations this tour visits</p>
            <div class="flex flex-wrap gap-x-6 gap-y-1">
                @foreach($experiences as $id => $label)
                    <label class="inline-flex min-h-11 items-center gap-2"><input type="checkbox" name="experience_ids[]" value="{{ $id }}" class="size-5 accent-ink" @checked(in_array($id, old('experience_ids', $package->exists ? $package->experiences->pluck('id')->all() : [])))> {{ $label }}</label>
                @endforeach
            </div>
        </div>
        <x-admin.input name="route" label="Route (cities, shown on the journey card)" :value="$package->route" help="Separate places with a middle dot, e.g. Bruges · Ghent" />
        <x-admin.textarea name="summary" label="Short summary (shown on cards)" :value="$package->summary" :rows="3" />
        <div class="flex flex-wrap gap-x-8">
            <x-admin.check name="is_published" label="Published" :checked="$package->is_published" />
            <x-admin.check name="is_featured" label="Featured on home page" :checked="$package->is_featured" />
        </div>
    </section>

    <section class="space-y-4 rounded-2xl bg-paper p-6 shadow-card">
        <h2 class="text-xl">Content</h2>
        <x-admin.rich name="description" label="Full description" :value="$package->description" />
        <x-admin.textarea name="highlights" label="Highlights (one per line)" :value="$hl" :rows="5" />
        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.textarea name="inclusions" label="What is included (one per line)" :value="$inc" :rows="6" />
            <x-admin.textarea name="exclusions" label="What is not included (one per line)" :value="$exc" :rows="6" />
        </div>
    </section>

    <section class="space-y-4 rounded-2xl bg-paper p-6 shadow-card" x-data="{ rows: @js(old('itinerary', $package->itinerary ?: [['title' => '', 'text' => '']])) }">
        <h2 class="text-xl">Day-by-day itinerary</h2>
        <template x-for="(row, i) in rows" :key="i">
            <div class="space-y-2 rounded-xl border-2 border-slate/20 p-3">
                <label class="block text-sm font-semibold">Day title<input :name="`itinerary[${i}][title]`" x-model="row.title" class="mt-1 block min-h-11 w-full rounded-xl border-2 border-slate/30 px-3"></label>
                <label class="block text-sm font-semibold">What happens<textarea :name="`itinerary[${i}][text]`" x-model="row.text" rows="3" class="mt-1 block w-full rounded-xl border-2 border-slate/30 px-3 py-2"></textarea></label>
                <button type="button" class="text-sm font-semibold text-red-800 underline" @click="rows.splice(i, 1)">Remove day</button>
            </div>
        </template>
        <x-button type="button" variant="sky" size="sm" @click="rows.push({ title: '', text: '' })">Add a day</x-button>
    </section>

    <section class="space-y-4 rounded-2xl bg-paper p-6 shadow-card" x-data="{ rows: @js(old('faqs', $package->faqs ?: [])) }">
        <h2 class="text-xl">Questions about this trip</h2>
        <template x-for="(row, i) in rows" :key="i">
            <div class="space-y-2 rounded-xl border-2 border-slate/20 p-3">
                <label class="block text-sm font-semibold">Question<input :name="`faqs[${i}][question]`" x-model="row.question" class="mt-1 block min-h-11 w-full rounded-xl border-2 border-slate/30 px-3"></label>
                <label class="block text-sm font-semibold">Answer<textarea :name="`faqs[${i}][answer]`" x-model="row.answer" rows="2" class="mt-1 block w-full rounded-xl border-2 border-slate/30 px-3 py-2"></textarea></label>
                <button type="button" class="text-sm font-semibold text-red-800 underline" @click="rows.splice(i, 1)">Remove question</button>
            </div>
        </template>
        <x-button type="button" variant="sky" size="sm" @click="rows.push({ question: '', answer: '' })">Add a question</x-button>
    </section>

    <section class="space-y-4 rounded-2xl bg-paper p-6 shadow-card">
        <h2 class="text-xl">Photos</h2>
        <div class="space-y-2 rounded-xl border-2 border-dashed border-slate/30 p-4">
            <p class="font-semibold">Hero photo</p>
            @if($package->hero_image)<x-photo :base="$package->hero_image" :alt="$package->hero_image_alt ?? ''" sizes="240px" class="h-32 w-48 rounded-xl" />@endif
            <input type="file" name="hero_image" accept="image/jpeg,image/png,image/webp" class="block text-sm">
            <x-admin.input name="hero_image_alt" label="Describe the hero photo (alt text, required with a new photo)" :value="$package->hero_image_alt" />
        </div>

        @if($package->exists && $package->images->isNotEmpty())
            <p class="font-semibold">Gallery</p>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach($package->images as $img)
                    <div class="space-y-2 rounded-xl border-2 border-slate/20 p-3">
                        <x-photo :base="$img->path" :alt="$img->alt" sizes="240px" class="h-28 w-full rounded-lg" />
                        <x-admin.input :name="'existing['.$img->id.'][alt]'" label="Description (alt text)" :value="$img->alt" :required="true" />
                        <div class="flex items-end gap-3">
                            <x-admin.input :name="'existing['.$img->id.'][sort]'" type="number" label="Order" :value="$img->sort" class="w-24" />
                            <button type="submit" form="del-{{ $img->id }}" class="min-h-11 text-sm font-semibold text-red-800 underline">Remove</button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div x-data="{ rows: [{}] }" class="space-y-2">
            <p class="font-semibold">Add gallery photos</p>
            <template x-for="(row, i) in rows" :key="i">
                <div class="grid gap-2 rounded-xl border-2 border-slate/20 p-3 sm:grid-cols-2">
                    <input type="file" :name="`new_images[${i}][file]`" accept="image/jpeg,image/png,image/webp" class="text-sm">
                    <input type="text" :name="`new_images[${i}][alt]`" placeholder="Describe the photo (required)" class="min-h-11 rounded-xl border-2 border-slate/30 px-3">
                </div>
            </template>
            <x-button type="button" variant="sky" size="sm" @click="rows.push({})">Another photo</x-button>
        </div>
    </section>

    <section class="space-y-4 rounded-2xl bg-paper p-6 shadow-card">
        <h2 class="text-xl">Search and sharing</h2>
        <x-admin.input name="seo_title" label="Page title for Google" :value="$package->seo_title" help="Around 60 characters. Blank uses the package title." />
        <x-admin.textarea name="meta_description" label="Description for Google" :value="$package->meta_description" :rows="2" help="Around 155 characters." />
        <div class="space-y-1"><p class="text-sm font-semibold">Social sharing image (optional)</p>
            @if($package->social_image)<img src="{{ asset($package->social_image) }}" alt="" class="h-20 rounded-lg">@endif
            <input type="file" name="social_image" accept="image/jpeg,image/png,image/webp" class="block text-sm"></div>
        <x-admin.check name="noindex" label="Hide this page from search engines" :checked="$package->noindex" />
    </section>

    <div class="sticky bottom-0 flex gap-3 rounded-2xl bg-cream p-3 shadow-lift">
        <x-button type="submit">Save package</x-button>
        <x-button :href="route('admin.packages.index')" variant="outline">Back to list</x-button>
        @if($package->exists)<x-button :href="route('packages.show', $package->slug)" variant="sky" target="_blank">View on site</x-button>@endif
    </div>
</form>

@if($package->exists)
    @foreach($package->images as $img)
        <form id="del-{{ $img->id }}" method="POST" action="{{ route('admin.packages.images.destroy', [$package, $img]) }}" class="hidden">@csrf @method('DELETE')</form>
    @endforeach
    <form method="POST" action="{{ route('admin.packages.destroy', $package) }}" class="mt-6" onsubmit="return confirm('Delete this package permanently?')">@csrf @method('DELETE')<x-button type="submit" variant="outline" size="sm">Delete package</x-button></form>
@endif
@endsection
