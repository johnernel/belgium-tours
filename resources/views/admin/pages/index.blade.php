@extends('layouts.admin')
@section('title', 'Pages')
@section('heading', 'Pages')

@section('content')
<p class="mb-4 max-w-2xl text-slate">Edit the wording of each page. Testimonials and FAQs have their own screens in the menu. Terms and Privacy are starter templates: have them reviewed before launch.</p>
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach($pages as $p)
        <a href="{{ route('admin.pages.edit', $p) }}" class="rounded-2xl bg-paper p-5 shadow-card hover:bg-cream">
            <p class="font-display text-xl font-semibold">{{ $p->title }}</p>
            <p class="mt-1 text-sm text-slate">/{{ $p->slug === 'home' ? '' : $p->slug }}@if($p->noindex) &middot; hidden from search @endif</p>
        </a>
    @endforeach
</div>
@endsection
