{{-- Shared enquiry modal. Sharp, square corners on everything inside (the [&_*]:!rounded-none on the panel). Open it with $store.modal.open({ mode, tourType, packageId, packageName, heading }).
     Layout: photo panel with the heading on the left (from lg) or on top (below lg), the form on the right. Only the form column scrolls, so the heading and the close button stay in view. The compact field grid is in app.css (.quote-popup). --}}
<div x-cloak x-show="$store.modal.show" class="fixed inset-0 z-[90] flex items-end justify-center sm:items-center sm:p-6"
     role="dialog" aria-modal="true" aria-labelledby="modal-title" @keydown.escape.window="$store.modal.close()">
    <div class="absolute inset-0 bg-ink/70" x-show="$store.modal.show" x-transition.opacity @click="$store.modal.close()"></div>
    <div class="quote-popup relative flex max-h-[94vh] w-full flex-col overflow-hidden bg-cream shadow-lift supports-[height:100dvh]:max-h-[94dvh] sm:max-w-2xl lg:max-w-[64rem] lg:flex-row [&_*]:!rounded-none"
         x-show="$store.modal.show" x-transition:enter="transition duration-200" x-transition:enter-start="translate-y-6 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
         x-trap.noscroll.inert="$store.modal.show">
        <button type="button" class="absolute right-2 top-2 z-10 grid size-11 place-items-center text-white hover:bg-white/15 focus-visible:outline-white lg:text-ink lg:hover:bg-ink/10 lg:focus-visible:outline-ink" @click="$store.modal.close()" aria-label="Close">
            @icon('x')
        </button>

        <div class="relative isolate shrink-0 overflow-hidden bg-ink py-4 pl-5 pr-16 text-white sm:py-6 sm:pl-8 lg:flex lg:w-[21rem] lg:flex-col lg:justify-end lg:p-8">
            {{-- Lazy: the popup is hidden until opened, so the photo is only downloaded then. --}}
            <x-photo base="images/photos/bruges-canal" alt="" sizes="(min-width: 1024px) 800px, 100vw" class="absolute inset-0 -z-20 size-full object-[72%_50%]" />
            <div class="absolute inset-0 -z-10 bg-ink/75 lg:bg-transparent lg:bg-gradient-to-t lg:from-ink lg:via-ink/80 lg:to-ink/20" aria-hidden="true"></div>

            <h2 id="modal-title" class="text-[1.35rem] leading-[1.2] text-white sm:text-3xl lg:text-[2.15rem] lg:leading-[1.15]"
                x-text="$store.modal.heading || ($store.modal.mode === 'question' ? 'Ask us a question' : 'Fill out this form and {{ $site->discount }}')">Fill out this form and {{ $site->discount }}</h2>
            <p class="mt-2 text-sm font-semibold text-amber-light" x-show="$store.modal.packageName" x-text="'About: ' + $store.modal.packageName"></p>
            <p class="mt-2 text-sm leading-relaxed text-white/90 max-sm:hidden lg:mt-3 lg:text-base" x-show="!$store.modal.packageName">Tell us a little about your trip. A travel specialist replies with a free quote.</p>

            <ul class="mt-6 hidden space-y-3 border-t border-white/25 pt-6 text-sm lg:block">
                @foreach(['Free to request, no obligation', 'Your details stay private', 'No spam, ever'] as $point)
                    <li class="flex items-center gap-3"><span class="grid size-5 shrink-0 place-items-center bg-cta text-white" aria-hidden="true">@icon('check', 'size-3.5')</span>{{ $point }}</li>
                @endforeach
            </ul>
            {{-- Only real contact details (Admin > Settings); nothing is shown until they are saved. --}}
            @if($site->phone || $site->email)
                <div class="mt-6 hidden space-y-1 border-t border-white/25 pt-5 text-sm lg:block">
                    @if($site->phone)<a href="tel:{{ $site->phone_href }}" class="flex min-h-8 items-center gap-3 hover:text-amber-light">@icon('phone', 'size-4'){{ $site->phone }}</a>@endif
                    @if($site->email)<a href="mailto:{{ $site->email }}" class="flex min-h-8 items-center gap-3 break-all hover:text-amber-light">@icon('mail', 'size-4'){{ $site->email }}</a>@endif
                </div>
            @endif
        </div>

        <div class="min-h-0 min-w-0 flex-1 overflow-y-auto overscroll-contain p-5 sm:px-8 sm:py-7 lg:pt-14">
            <x-enquiry-form form-name="modal" mode="modal" :extended="true" id-prefix="m" submit-label="Send my request" submit-variant="cta" />
        </div>
    </div>
</div>
