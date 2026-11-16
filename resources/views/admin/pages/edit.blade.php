@extends('layouts.admin')
@section('title', 'Edit '.$page->title)
@section('heading', 'Edit page: '.$page->title)

@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.pages.update', $page) }}" class="max-w-4xl space-y-6">
    @csrf @method('PUT')
    <section class="space-y-4 rounded-2xl bg-paper p-6 shadow-card">
        <h2 class="text-xl">Page</h2>
        <x-admin.input name="title" label="Page heading" :value="$page->title" :required="true" />
        @if($page->slug !== 'home')<x-admin.rich name="body" label="Content" :value="$page->body" />@endif
        @if($page->slug === 'terms' || $page->slug === 'privacy')<p class="rounded-lg bg-amber/30 p-3 text-sm">Type [Company Name] anywhere and it becomes your legal company name. If you change the legal wording, also bump the terms version in config/gotour.php so new enquiries record which version they agreed to.</p>@endif
    </section>

    @if(in_array($page->slug, ['about', 'contact'], true))
        <section class="space-y-4 rounded-2xl bg-paper p-6 shadow-card">
            <h2 class="text-xl">Banner at the top of the page</h2>
            <x-admin.textarea name="content[hero_sub]" label="Short description under the heading" :value="$page->block('hero_sub')" :rows="2" help="Blank uses the standard wording." />
            <div class="space-y-2 rounded-xl border-2 border-dashed border-slate/30 p-4">
                <p class="text-sm font-semibold">Banner photo</p>
                @php $heroBase = $page->block('hero_image', $page->slug === 'about' ? 'images/photos/gravensteen' : 'images/photos/brussels-grand-place'); @endphp
                <x-photo :base="$heroBase" :alt="$page->block('hero_image_alt')" sizes="240px" class="h-32 w-48 rounded-xl" />
                <input type="file" name="hero_image" accept="image/jpeg,image/png,image/webp" class="block text-sm">
                <x-admin.input name="content[hero_image_alt]" label="Describe the photo (alt text, required with a new photo)" :value="$page->block('hero_image_alt')" />
                <p class="text-xs text-slate">Wide photos work best. Free-licence or your own photos only.</p>
            </div>
        </section>
    @endif

    @if($page->slug === 'home' && $page->content)
        <section class="space-y-4 rounded-2xl bg-paper p-6 shadow-card">
            <h2 class="text-xl">Home page wording</h2>
            <p class="text-sm text-slate">Each box is one piece of text on the home page. Anything in [square brackets] is a placeholder to confirm. In headings, put *asterisks* around the part that should be the coloured italic accent, for example Three Ways to *Travel* Belgium. With no asterisks the last word is the accent.</p>
            @foreach($page->content as $key => $val)
                @if(strlen($val) > 90)<x-admin.textarea :name="'content['.$key.']'" :label="ucfirst(str_replace('_', ' ', $key))" :value="$val" :rows="3" />
                @else<x-admin.input :name="'content['.$key.']'" :label="ucfirst(str_replace('_', ' ', $key))" :value="$val" />@endif
            @endforeach
        </section>
    @endif

    <section class="space-y-4 rounded-2xl bg-paper p-6 shadow-card">
        <h2 class="text-xl">Search and sharing</h2>
        <x-admin.input name="seo_title" label="Page title for Google" :value="$page->seo_title" help="Around 60 characters." />
        <x-admin.textarea name="meta_description" label="Description for Google" :value="$page->meta_description" :rows="2" help="Around 155 characters." />
        <x-admin.check name="noindex" label="Hide this page from search engines" :checked="$page->noindex" />
    </section>
    <div class="flex gap-3"><x-button type="submit">Save page</x-button><x-button :href="route('admin.pages.index')" variant="outline">Back</x-button></div>
</form>
@endsection
