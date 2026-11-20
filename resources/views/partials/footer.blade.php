@php $footTypes = \App\Models\TourType::active()->get(); @endphp
<footer class="{{ $__env->hasSection('footer_flush') ? '' : 'mt-24' }} bg-forest text-cream">
    <div class="container-page grid gap-12 py-14 sm:grid-cols-2 lg:grid-cols-[1.6fr_1fr_1fr] lg:gap-16">
        {{-- Brand: logo, tagline and (when set) contact details --}}
        <div class="sm:col-span-2 lg:col-span-1">
            <a href="{{ route('home') }}" class="inline-block" aria-label="{{ $site->brand }} home">
                @if($site->logo)
                    {{-- An uploaded logo wins; it is turned white because the footer is dark --}}
                    <img src="{{ asset($site->logo) }}" alt="{{ $site->brand }}" class="h-12 w-auto brightness-0 invert sm:h-14" width="160" height="40" loading="lazy">
                @else
                    {{-- White-text version of the default logo (same file the transparent header uses) --}}
                    <img src="{{ asset('images/logo-white.webp') }}?v={{ @filemtime(public_path('images/logo-white.webp')) }}" alt="{{ $site->brand }}" class="h-12 w-auto sm:h-14" width="484" height="152" loading="lazy">
                @endif
            </a>
            @if($site->footer_text)<p class="mt-4 max-w-md text-lg text-cream/90">{{ $site->footer_text }}</p>@endif
            <ul class="mt-5 space-y-2 text-cream/90">
                @if($site->phone)<li><a class="inline-flex min-h-8 items-center gap-2 hover:text-amber" href="tel:{{ $site->phone_href }}">@icon('phone', 'size-4') {{ $site->phone }}</a></li>@endif
                @if($site->email)<li><a class="inline-flex min-h-8 items-center gap-2 hover:text-amber" href="mailto:{{ $site->email }}">@icon('mail', 'size-4') {{ $site->email }}</a></li>@endif
                @if($site->address)<li class="flex items-start gap-2">@icon('map-pin', 'mt-1 size-4 shrink-0') {{ $site->address }}</li>@endif
            </ul>
        </div>

        <nav aria-labelledby="foot-explore">
            <p id="foot-explore" class="flex items-center gap-4 text-xs font-semibold uppercase tracking-[0.22em] text-amber-light"><span class="h-px w-9 bg-amber" aria-hidden="true"></span>Explore</p>
            <ul class="mt-6 space-y-1.5 text-lg">
                <li><a class="inline-block py-1 hover:text-amber" href="{{ route('packages.index') }}">All packages</a></li>
                @foreach($footTypes as $t)<li><a class="inline-block py-1 hover:text-amber" href="{{ route('tours.show', $t->slug) }}">{{ $t->name }}</a></li>@endforeach
                <li><a class="inline-block py-1 hover:text-amber" href="{{ route('destinations.index') }}">Destinations</a></li>
                <li><a class="inline-block py-1 hover:text-amber" href="{{ route('journal.index') }}">Travel guides</a></li>
            </ul>
        </nav>

        <nav aria-labelledby="foot-about">
            <p id="foot-about" class="flex items-center gap-4 text-xs font-semibold uppercase tracking-[0.22em] text-amber-light"><span class="h-px w-9 bg-amber" aria-hidden="true"></span>About Us</p>
            <ul class="mt-6 space-y-1.5 text-lg">
                <li><a class="inline-block py-1 hover:text-amber" href="{{ route('about') }}">About Us</a></li>
                <li><a class="inline-block py-1 hover:text-amber" href="{{ route('team') }}">Meet Our Team</a></li>
                <li><a class="inline-block py-1 hover:text-amber" href="{{ route('testimonials') }}">Testimonials</a></li>
                <li><a class="inline-block py-1 hover:text-amber" href="{{ route('faqs') }}">FAQs</a></li>
            </ul>
        </nav>
    </div>

    <div class="border-t border-cream/15">
        <div class="container-page flex flex-col gap-3 py-6 text-[0.95rem] text-cream/90 lg:flex-row lg:items-center lg:justify-between">
            <p>&copy; {{ date('Y') }} {{ $site->company }}. All rights reserved.</p>
            <nav aria-label="Legal and contact" class="flex flex-wrap gap-x-6 gap-y-1">
                <a class="underline underline-offset-4 hover:text-amber" href="{{ route('about') }}">About</a>
                <a class="underline underline-offset-4 hover:text-amber" href="{{ route('contact') }}">Contact</a>
                <a class="underline underline-offset-4 hover:text-amber" href="{{ route('terms') }}">Terms and Conditions</a>
                <a class="underline underline-offset-4 hover:text-amber" href="{{ route('privacy') }}">Privacy Policy</a>
                <a class="underline underline-offset-4 hover:text-amber" href="{{ route('about') }}#photo-credits">Photo credits</a>
            </nav>
        </div>
    </div>
</footer>
