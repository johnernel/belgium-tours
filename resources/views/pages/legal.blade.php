@extends('layouts.app')

@section('title', $page->seo_title ?: $page->title)
@section('description', $page->meta_description)
@if($page->noindex)@section('noindex', '1')@endif

@section('content')
<section class="py-12 sm:py-16">
    <div class="container-page">
        <x-section-heading tag="h1" :title="$page->title" />
        <p class="mt-2 text-sm text-slate">Version {{ config('gotour.terms_version') }}</p>
        {{-- [Company Name] markers in the stored text are replaced with the legal company name. --}}
        <div class="prose-page mt-8">{!! str_replace('[Company Name]', e($site->company), $page->body) !!}</div>
    </div>
</section>
@endsection
