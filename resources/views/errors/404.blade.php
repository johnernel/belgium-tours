@extends('layouts.app')

@section('title', 'Page not found | '.$site->brand)
@section('noindex', '1')

@section('content')
<section class="py-20 sm:py-28">
    <div class="container-page max-w-2xl text-center">
        <h1 class="text-heading">We took a wrong turn</h1>
        <p class="mt-4 text-lg text-slate">That page does not exist or has moved. Try one of these instead.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <x-button :href="route('home')">Back to home</x-button>
            <x-button :href="route('packages.index')" variant="outline">Browse packages</x-button>
        </div>
    </div>
</section>
@endsection
