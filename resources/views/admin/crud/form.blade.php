@extends('layouts.admin')
@section('title', ($record->exists ? 'Edit ' : 'Add ').$cfg['singular'])
@section('heading', ($record->exists ? 'Edit ' : 'Add ').strtolower($cfg['singular']))

@section('content')
<form method="POST" enctype="multipart/form-data" class="max-w-2xl space-y-5 rounded-2xl bg-paper p-6 shadow-card"
      action="{{ $record->exists ? route($routeBase.'.update', $record) : route($routeBase.'.store') }}">
    @csrf
    @if($record->exists)@method('PUT')@endif

    @foreach($cfg['fields'] as $f)
        @php $n = $f['name']; @endphp
        @switch($f['type'])
            @case('textarea')<x-admin.textarea :name="$n" :label="$f['label']" :value="$record->{$n}" :help="$f['help'] ?? null" />@break
            @case('rich')<x-admin.rich :name="$n" :label="$f['label']" :value="$record->{$n}" />@break
            @case('number')<x-admin.input :name="$n" type="number" :label="$f['label']" :value="$record->{$n}" />@break
            @case('checkbox')<x-admin.check :name="$n" :label="$f['label']" :checked="(bool) $record->{$n}" />@break
            @case('relation')
                {{-- A list of tick boxes for a many-to-many link (e.g. the tours of a destination). The hidden marker tells the controller this list was on the form, so a save from somewhere else never wipes the links. --}}
                @php $chosen = array_map('intval', (array) old($n, $record->exists ? $record->{$f['relation']}()->pluck($f['relation'].'.id')->all() : [])); @endphp
                <fieldset>
                    <legend class="mb-1 text-sm font-semibold">{{ $f['label'] }}</legend>
                    @if(!empty($f['help']))<p class="mb-2 text-sm text-slate">{{ $f['help'] }}</p>@endif
                    <input type="hidden" name="{{ $n }}_present" value="1">
                    <div class="grid max-h-80 gap-x-6 overflow-y-auto sm:grid-cols-2">
                        @foreach($f['options']() as $id => $label)
                            <label class="inline-flex min-h-11 items-center gap-2"><input type="checkbox" name="{{ $n }}[]" value="{{ $id }}" class="size-5 accent-ink" @checked(in_array($id, $chosen, true))> <span>{{ $label }}</span></label>
                        @endforeach
                    </div>
                    @error($n)<p class="mt-1 text-sm font-semibold text-red-800">{{ $message }}</p>@enderror
                </fieldset>
            @break
            @case('image')
                <div class="space-y-2 rounded-xl border-2 border-dashed border-slate/30 p-4">
                    <p class="text-sm font-semibold">{{ $f['label'] }}</p>
                    @if($record->{$n})<x-photo :base="$record->{$n}" :alt="$record->{$f['alt']} ?? ''" sizes="200px" class="h-32 w-48 rounded-xl" />
                        <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="{{ $n }}_remove" value="1" class="accent-ink"> Remove current photo</label>@endif
                    <input type="file" name="{{ $n }}" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm">
                    <x-admin.input :name="$f['alt']" label="Describe the photo (alt text, required with a new photo)" :value="$record->{$f['alt']}" />
                    <p class="text-xs text-slate">JPG, PNG or WebP up to 8 MB. It is resized and converted automatically.</p>
                </div>
            @break
            @default<x-admin.input :name="$n" :label="$f['label']" :value="$record->{$n}" :required="$f['required'] ?? false" :help="$f['help'] ?? null" />
        @endswitch
    @endforeach

    <div class="flex gap-3"><x-button type="submit">Save</x-button><x-button :href="route($routeBase.'.index')" variant="outline">Cancel</x-button></div>
</form>
@endsection
