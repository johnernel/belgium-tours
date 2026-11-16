@extends('layouts.app')

@section('title', 'Thank you | '.$site->brand)
@section('description', 'Thanks for your enquiry. We will be in touch soon.')
@section('noindex', '1')

@push('scripts')
    {{-- Conversion event for Google Tag Manager: attach the Google Ads conversion to this trigger in GTM. --}}
    <script>
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({ event: 'enquiry_thank_you' });
    </script>
@endpush

@section('content')
<section class="py-20 sm:py-28">
    <div class="container-page max-w-2xl text-center">
        <span class="mx-auto grid size-16 place-items-center rounded-full bg-sky text-ink">@icon('check', 'size-8')</span>
        <h1 class="mt-6 text-heading">Thank you, we have your enquiry</h1>
        <p class="mt-4 text-lg text-slate">A travel specialist will reply with your free quote soon. Check your inbox, and your spam folder just in case.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <x-button :href="route('packages.index')">Browse packages</x-button>
            <x-button :href="route('home')" variant="outline">Back to home</x-button>
        </div>
    </div>
</section>
@endsection
