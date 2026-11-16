@php $footTypes = \App\Models\TourType::active()->get(); @endphp
<footer class="{{ $__env->hasSection('footer_flush') ? '' : 'mt-24' }} bg-ink text-cream">
    <div class="container-page grid gap-10 py-14 md:grid-cols-4">
        <div class="md:col-span-2">
            <a href="{{ route('home') }}" class="inline-block" aria-label="{{ $site->brand }} home">
                @if($site->logo)
                    {{-- An uploaded logo wins; it is turned white because the footer is dark --}}
                    <img src="{{ asset($site->logo) }}" alt="{{ $site->brand }}" class="h-12 w-auto brightness-0 invert sm:h-14" width="160" height="40" loading="lazy">
                @else
                    {{-- White-text version of the default logo (same file the transparent header uses) --}}
                    <img src="{{ asset('images/logo-white.webp') }}" alt="{{ $site->brand }}" class="h-12 w-auto sm:h-14" width="556" height="152" loading="lazy">
                @endif
            </a>
            @if($site->footer_text)<p class="mt-3 max-w-md text-cream/80">{{ $site->footer_text }}</p>@endif
            <ul class="mt-5 space-y-2 text-cream/90">
                @if($site->phone)<li><a class="inline-flex min-h-8 items-center gap-2 hover:text-amber" href="tel:{{ $site->phone_href }}">@icon('phone', 'size-4') {{ $site->phone }}</a></li>@endif
                @if($site->email)<li><a class="inline-flex min-h-8 items-center gap-2 hover:text-amber" href="mailto:{{ $site->email }}">@icon('mail', 'size-4') {{ $site->email }}</a></li>@endif
                @if($site->address)<li class="flex items-start gap-2">@icon('map-pin', 'mt-1 size-4 shrink-0') {{ $site->address }}</li>@endif
            </ul>
        </div>
        <nav aria-label="Tours">
            <p class="font-display text-lg font-semibold">Tour Types</p>
            <ul class="mt-3 space-y-1">
                @foreach($footTypes as $t)<li><a class="inline-block py-1 hover:text-amber" href="{{ route('tours.show', $t->slug) }}">{{ $t->name }}</a></li>@endforeach
                <li><a class="inline-block py-1 hover:text-amber" href="{{ route('packages.index') }}">All packages</a></li>
            </ul>
        </nav>
        <nav aria-label="Company">
            <p class="font-display text-lg font-semibold">Company</p>
            <ul class="mt-3 space-y-1">
                <li><a class="inline-block py-1 hover:text-amber" href="{{ route('destinations.index') }}">Destinations</a></li>
                <li><a class="inline-block py-1 hover:text-amber" href="{{ route('about') }}">About us</a></li>
                <li><a class="inline-block py-1 hover:text-amber" href="{{ route('contact') }}">Contact</a></li>
                <li><a class="inline-block py-1 hover:text-amber" href="{{ route('terms') }}">Terms and Conditions</a></li>
                <li><a class="inline-block py-1 hover:text-amber" href="{{ route('privacy') }}">Privacy Policy</a></li>
            </ul>
        </nav>
    </div>
    <div class="border-t border-cream/15">
        <div class="container-page flex flex-col gap-1 py-5 text-sm text-cream/75 sm:flex-row sm:justify-between">
            <p>&copy; {{ date('Y') }} {{ $site->company }}. All rights reserved.</p>
            <p>Photos: <a class="underline hover:text-amber" href="{{ route('about') }}#photo-credits">credits</a></p>
        </div>
    </div>
</footer>
