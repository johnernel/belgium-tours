@props([
    'formName' => 'contact',
    'package' => null,        // Package model for package quote forms
    'tourType' => null,       // tour type name to pre-fill
    'mode' => 'standalone',   // standalone | modal | quiz
    'extended' => false,      // adds time of year, group size and nights
    'submitLabel' => 'Get my free quote',
    'idPrefix' => 'f',
])
@php
    $id = fn ($n) => $idPrefix.'-'.$n;
    $countries = config('gotour.countries');
    $siteKey = $site->recaptcha_key ?? null;
@endphp
<form method="POST" action="{{ route('enquiry.store') }}" novalidate
      x-data="enquiryForm({ formName: '{{ $formName }}', captcha: '{{ $mode === 'quiz' ? 'now' : ($mode === 'modal' ? 'modal' : 'touch') }}' })" x-ref="form" @submit.prevent="submit()"
      class="space-y-4" aria-label="Enquiry form">
    @csrf
    <input type="hidden" name="form_name" @if($mode === 'modal') x-bind:value="$store.modal.mode === 'question' ? 'question' : '{{ $formName }}'" @endif value="{{ $formName }}">
    <input type="hidden" name="page_url" value="{{ url()->current() }}">

    @if($mode === 'quiz')
        <input type="hidden" name="answers" x-bind:value="answersJson">
    @endif

    @if($mode === 'modal')
        <input type="hidden" name="package_id" x-bind:value="$store.modal.packageId">
        <input type="hidden" name="tour_type" x-bind:value="$store.modal.tourType">
    @else
        @if($package)<input type="hidden" name="package_id" value="{{ $package->id }}">@endif
        @if($tourType || $package?->tourType)<input type="hidden" name="tour_type" value="{{ $tourType ?? $package?->tourType?->name }}">@endif
    @endif

    {{-- Honeypot: hidden from people, tempting for bots. --}}
    <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
        <label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <x-field :id="$id('first_name')" name="first_name" label="First name" required autocomplete="given-name" />
        <x-field :id="$id('last_name')" name="last_name" label="Last name" required autocomplete="family-name" />
    </div>

    <x-field :id="$id('email')" name="email" type="email" label="Email" required autocomplete="email" inputmode="email" />

    <div>
        <label for="{{ $id('phone') }}" class="mb-1.5 block text-sm font-semibold">Phone <span aria-hidden="true">*</span></label>
        <div class="flex gap-2">
            <select name="country" aria-label="Country code" autocomplete="country"
                    class="min-h-11 w-[8.5rem] shrink-0 rounded-xl border-2 border-slate/30 bg-white px-2 py-2.5 text-base focus:border-ink focus:outline-none">
                @foreach($countries as [$iso, $name, $dial])
                    <option value="{{ $iso }}" @selected(old('country', 'US') === $iso)>{{ $iso }} +{{ $dial }}</option>
                @endforeach
            </select>
            <input id="{{ $id('phone') }}" name="phone" type="tel" inputmode="tel" required autocomplete="tel-national"
                   value="{{ old('phone') }}" placeholder="Phone number"
                   x-bind:aria-invalid="fieldError('phone') ? 'true' : 'false'"
                   class="block min-h-11 w-full min-w-0 rounded-xl border-2 border-slate/30 bg-white px-3.5 py-2.5 text-base placeholder:text-slate/60 focus:border-ink focus:outline-none">
        </div>
        <p class="mt-1 text-sm font-semibold text-red-800" x-show="fieldError('phone')" x-text="fieldError('phone')" role="alert"></p>
        <p class="mt-1 text-sm font-semibold text-red-800" x-show="fieldError('country')" x-text="fieldError('country')" role="alert"></p>
        @error('phone')<p class="mt-1 text-sm font-semibold text-red-800">{{ $message }}</p>@enderror
    </div>

    <x-field :id="$id('travel_date')" name="travel_date" label="Approximate travel date" placeholder="e.g. June 2027" />

    @if($extended)
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label for="{{ $id('season') }}" class="mb-1.5 block text-sm font-semibold">Time of year</label>
                <select id="{{ $id('season') }}" name="season" class="min-h-11 w-full rounded-xl border-2 border-slate/30 bg-white px-3 py-2.5 text-base focus:border-ink focus:outline-none">
                    <option value="">Not sure</option>
                    @foreach(['Spring', 'Summer', 'Autumn', 'Winter'] as $s)<option>{{ $s }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="{{ $id('group_size') }}" class="mb-1.5 block text-sm font-semibold">Group size</label>
                <select id="{{ $id('group_size') }}" name="group_size" class="min-h-11 w-full rounded-xl border-2 border-slate/30 bg-white px-3 py-2.5 text-base focus:border-ink focus:outline-none">
                    <option value="">Not sure</option>
                    @foreach(range(1, 10) as $n)<option value="{{ $n }}">{{ $n === 10 ? '10 or more' : $n }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="{{ $id('nights') }}" class="mb-1.5 block text-sm font-semibold">Nights</label>
                <select id="{{ $id('nights') }}" name="nights" class="min-h-11 w-full rounded-xl border-2 border-slate/30 bg-white px-3 py-2.5 text-base focus:border-ink focus:outline-none">
                    <option value="">Not sure</option>
                    @foreach(range(3, 14) as $n)<option value="{{ $n }}">{{ $n === 14 ? '14 or more' : $n }}</option>@endforeach
                </select>
            </div>
        </div>
    @endif

    <div>
        <label for="{{ $id('second_quote') }}" class="mb-1.5 block text-sm font-semibold">Would you like a second quote from a partner?</label>
        <select id="{{ $id('second_quote') }}" name="second_quote" class="min-h-11 w-full rounded-xl border-2 border-slate/30 bg-white px-3 py-2.5 text-base focus:border-ink focus:outline-none">
            <option value="">Choose one</option>
            <option value="yes" @selected(old('second_quote') === 'yes')>Yes, please</option>
            <option value="no" @selected(old('second_quote') === 'no')>No, thanks</option>
            <option value="maybe" @selected(old('second_quote') === 'maybe')>Maybe later</option>
        </select>
    </div>

    <div>
        <label for="{{ $id('message') }}" class="mb-1.5 block text-sm font-semibold">
            <span x-text="$store.modal && $store.modal.show && $store.modal.mode === 'question' ? 'Your question' : 'Special requests'">Special requests</span>
        </label>
        <textarea id="{{ $id('message') }}" name="message" rows="3" maxlength="3000"
                  class="block w-full rounded-xl border-2 border-slate/30 bg-white px-3.5 py-2.5 text-base focus:border-ink focus:outline-none">{{ old('message') }}</textarea>
    </div>

    {{-- Required consent. Exact wording comes from config/gotour.php. Never pre-ticked. --}}
    <div>
        <div class="flex items-start gap-3">
            <input id="{{ $id('consent') }}" name="consent" type="checkbox" value="1" required x-model="consent" x-ref="consent"
                   x-bind:aria-invalid="fieldError('consent') ? 'true' : 'false'"
                   class="mt-1 size-5 shrink-0 rounded border-2 border-slate accent-ink">
            <label for="{{ $id('consent') }}" class="text-[0.8125rem] leading-snug text-slate">{!! \App\Support\Consent::html() !!}</label>
        </div>
        <p class="mt-1 text-sm font-semibold text-red-800" x-show="fieldError('consent')" x-text="fieldError('consent')" role="alert"></p>
        @error('consent')<p class="mt-1 text-sm font-semibold text-red-800">{{ $message }}</p>@enderror
    </div>

    @if($siteKey)
        <div>
            <div x-ref="captcha" class="min-h-[78px]"></div>
            <noscript>
                <iframe src="https://www.google.com/recaptcha/api/fallback?k={{ $siteKey }}" title="reCAPTCHA" class="h-[422px] w-full max-w-[302px] border-0"></iframe>
                <textarea name="g-recaptcha-response" class="sr-only" aria-hidden="true"></textarea>
            </noscript>
            <p class="mt-1 text-sm font-semibold text-red-800" x-show="fieldError('g-recaptcha-response')" x-text="fieldError('g-recaptcha-response')" role="alert"></p>
            @error('g-recaptcha-response')<p class="mt-1 text-sm font-semibold text-red-800">{{ $message }}</p>@enderror
        </div>
    @else
        <p class="rounded-lg bg-amber/30 p-3 text-sm font-semibold">reCAPTCHA keys are not set, so this form cannot be submitted. Add them in Admin &gt; Settings or in .env.</p>
    @endif

    <p class="rounded-lg bg-red-50 p-3 text-sm font-semibold text-red-900" x-show="generalError" x-text="generalError" role="alert"></p>

    <div x-show="!done">
        <x-button type="submit" size="lg" class="w-full" x-bind:disabled="submitting" x-text="submitting ? 'Sending…' : '{{ $submitLabel }}'">{{ $submitLabel }}</x-button>
    </div>
    <p x-cloak x-show="done" role="status" class="rounded-xl bg-sky-soft p-4 text-center font-display text-lg font-medium">Thank you! Your enquiry is on its way…</p>
</form>
