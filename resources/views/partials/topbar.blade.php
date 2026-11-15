{{-- Slim bar: contact details left, quote actions right. Hidden items are simply omitted until set in Admin > Settings. --}}
<div class="bg-ink text-sm text-cream">
    <div class="container-page flex flex-wrap items-center justify-between gap-x-6 gap-y-1 py-2">
        <div class="flex flex-wrap items-center gap-x-5 gap-y-1">
            @if($site->phone)
                <a href="tel:{{ $site->phone_href }}" class="inline-flex min-h-8 items-center gap-1.5 font-semibold hover:text-amber">
                    @icon('phone', 'size-4') {{ $site->phone }}
                </a>
            @endif
            @if($site->email)
                <a href="mailto:{{ $site->email }}" class="hidden min-h-8 items-center gap-1.5 hover:text-amber sm:inline-flex">
                    @icon('mail', 'size-4') {{ $site->email }}
                </a>
            @endif
            @unless($site->phone || $site->email)
                <span class="text-cream/80">{{ $site->tagline }}</span>
            @endunless
        </div>
        <div class="ml-auto flex items-center gap-4">
            <a href="{{ route('contact') }}" @click.prevent="$store.modal.open({ heading: 'Get your instant quote' })" class="font-semibold hover:text-amber">Get Instant Quote</a>
            <button type="button" @click="$store.modal.open()" class="hidden min-h-8 rounded-full bg-amber px-4 py-1 font-display font-semibold text-ink hover:bg-amber-deep sm:inline-block">Start Your Adventure</button>
        </div>
    </div>
</div>
