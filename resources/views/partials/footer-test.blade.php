{{-- Footer for the "Homepage Test" design: charcoal, a call-to-action strip, four columns, legal bar. Go Belgium logo (white version). --}}
@php $footTypes = \App\Models\TourType::active()->get(); @endphp
<footer class="{{ $__env->hasSection('footer_flush') ? '' : 'mt-24' }} bg-ink text-cream">
    <div class="border-b border-cream/15">
        <div class="container-page flex flex-col items-start justify-between gap-6 py-10 lg:flex-row lg:items-center">
            <div>
                <p class="font-display text-3xl leading-tight sm:text-4xl">Ready to plan your <span class="italic text-amber">Belgium</span> trip?</p>
                <p class="mt-2 text-cream/80">Tell us what you have in mind. It is free and there is no obligation.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <x-button variant="primary" size="lg" icon="arrow-right" @click="$store.modal.open()">Get a Free Quote</x-button>
                <x-button :href="route('packages.index')" variant="light" size="lg">Browse Packages</x-button>
            </div>
        </div>
    </div>

    <div class="container-page grid gap-12 py-14 sm:grid-cols-2 lg:grid-cols-[1.5fr_1fr_1fr_1fr] lg:gap-14">
        <div class="sm:col-span-2 lg:col-span-1">
            <a href="{{ route('home') }}" class="inline-block" aria-label="{{ $site->brand }} home">
                @if($site->logo)
                    <img src="{{ asset($site->logo) }}" alt="{{ $site->brand }}" class="h-12 w-auto brightness-0 invert sm:h-14" width="160" height="40" loading="lazy">
                @else
                    <img src="{{ asset('images/logo-white.webp') }}" alt="{{ $site->brand }}" class="h-12 w-auto sm:h-14" width="556" height="152" loading="lazy">
                @endif
            </a>
            @if($site->footer_text)<p class="mt-4 max-w-sm text-cream/85">{{ $site->footer_text }}</p>@endif
            <ul class="mt-5 space-y-2 text-cream/90">
                @if($site->phone)<li><a class="inline-flex min-h-8 items-center gap-2 hover:text-amber" href="tel:{{ $site->phone_href }}">@icon('phone', 'size-4') {{ $site->phone }}</a></li>@endif
                @if($site->email)<li><a class="inline-flex min-h-8 items-center gap-2 hover:text-amber" href="mailto:{{ $site->email }}">@icon('mail', 'size-4') {{ $site->email }}</a></li>@endif
                @if($site->address)<li class="flex items-start gap-2">@icon('map-pin', 'mt-1 size-4 shrink-0') {{ $site->address }}</li>@endif
            </ul>
        </div>

        <nav aria-labelledby="ft-types">
            <p id="ft-types" class="text-xs font-semibold uppercase tracking-[0.22em] text-amber">Tour Types</p>
            <ul class="mt-5 space-y-1.5">
                @foreach($footTypes as $t)<li><a class="inline-block py-1 hover:text-amber" href="{{ route('tours.show', $t->slug) }}">{{ $t->name }}</a></li>@endforeach
                <li><a class="inline-block py-1 hover:text-amber" href="{{ route('packages.index') }}">All packages</a></li>
            </ul>
        </nav>
        <nav aria-labelledby="ft-explore">
            <p id="ft-explore" class="text-xs font-semibold uppercase tracking-[0.22em] text-amber">Explore</p>
            <ul class="mt-5 space-y-1.5">
                <li><a class="inline-block py-1 hover:text-amber" href="{{ route('destinations.index') }}">Destinations</a></li>
                <li><a class="inline-block py-1 hover:text-amber" href="{{ route('journal.index') }}">Travel guides</a></li>
                <li><a class="inline-block py-1 hover:text-amber" href="{{ route('contact') }}">Contact</a></li>
            </ul>
        </nav>
        <nav aria-labelledby="ft-about">
            <p id="ft-about" class="text-xs font-semibold uppercase tracking-[0.22em] text-amber">About Us</p>
            <ul class="mt-5 space-y-1.5">
                <li><a class="inline-block py-1 hover:text-amber" href="{{ route('about') }}">About Us</a></li>
                <li><a class="inline-block py-1 hover:text-amber" href="{{ route('team') }}">Meet Our Team</a></li>
                <li><a class="inline-block py-1 hover:text-amber" href="{{ route('testimonials') }}">Testimonials</a></li>
                <li><a class="inline-block py-1 hover:text-amber" href="{{ route('faqs') }}">FAQs</a></li>
            </ul>
        </nav>
    </div>

    <div class="border-t border-cream/15">
        <div class="container-page flex flex-col gap-3 py-6 text-[0.95rem] text-cream/85 lg:flex-row lg:items-center lg:justify-between">
            <p>&copy; {{ date('Y') }} {{ $site->company }}. All rights reserved.</p>
            <nav aria-label="Legal" class="flex flex-wrap gap-x-6 gap-y-1">
                <a class="underline underline-offset-4 hover:text-amber" href="{{ route('terms') }}">Terms and Conditions</a>
                <a class="underline underline-offset-4 hover:text-amber" href="{{ route('privacy') }}">Privacy Policy</a>
                <a class="underline underline-offset-4 hover:text-amber" href="{{ route('about') }}#photo-credits">Photo credits</a>
            </nav>
        </div>
    </div>
</footer>
