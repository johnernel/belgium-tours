@extends('layouts.admin')
@section('title', 'reCAPTCHA')
@section('heading', 'reCAPTCHA keys')

@section('content')
<div class="max-w-3xl space-y-6">
    <section class="space-y-3 rounded-2xl bg-paper p-6 shadow-card" aria-labelledby="rc-status">
        <h2 id="rc-status" class="sr-only">Status</h2>
        <div class="flex flex-wrap items-center gap-3">
            @if(! $enabled)
                <span class="inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1 text-sm font-semibold text-red-900"><span class="size-2 rounded-full bg-red-600"></span>Off: forms are not protected by reCAPTCHA</span>
            @elseif(! $complete)
                <span class="inline-flex items-center gap-2 rounded-full bg-amber/30 px-3 py-1 text-sm font-semibold"><span class="size-2 rounded-full bg-amber-deep"></span>Keys missing: forms cannot be sent</span>
            @elseif($testKeys)
                <span class="inline-flex items-center gap-2 rounded-full bg-amber/30 px-3 py-1 text-sm font-semibold"><span class="size-2 rounded-full bg-amber-deep"></span>Google test keys in use: no real protection</span>
            @else
                <span class="inline-flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800"><span class="size-2 rounded-full bg-green-600"></span>Live keys in use{{ $source === 'admin' ? ' (saved here)' : ' (from the server .env)' }}</span>
            @endif
        </div>
        <p class="text-sm text-slate">If Google cannot be reached, the check fails closed: the enquiry is rejected, never let through. The switch below applies to every form on the site (the quiz, quote requests, questions and the contact form).</p>
        @if(! $enabled)<p class="rounded-xl bg-red-50 p-3 text-sm text-red-900">With reCAPTCHA off, only the hidden honeypot field and the rate limit (6 enquiries a minute per visitor) stop spam. Switch it back on before running ads.</p>@endif
        @if($testKeys && $enabled)<p class="rounded-xl bg-amber/30 p-3 text-sm">These are Google's public test keys: every check passes, including bots. Replace them with your own keys from <strong>google.com/recaptcha/admin</strong> (type: reCAPTCHA v2, "I'm not a robot" checkbox) and add this website's domain to the key.</p>@endif
    </section>

    <form method="POST" action="{{ route('admin.recaptcha.update') }}" class="space-y-6">
        @csrf @method('PUT')
        <section class="space-y-4 rounded-2xl bg-paper p-6 shadow-card" aria-labelledby="rc-keys">
            <h2 id="rc-keys" class="text-xl">Your keys</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.input name="site_key" label="Site key" :value="$source === 'admin' ? $siteKey : ''" autocomplete="off" help="The public key. Shown on the website." />
                <div>
                    <label for="a-secret_key" class="mb-1 block text-sm font-semibold">Secret key</label>
                    <input id="a-secret_key" name="secret_key" type="password" autocomplete="new-password" value="" placeholder="{{ $secretSaved ? $secretHint : 'Paste your secret key' }}"
                           class="block min-h-11 w-full rounded-xl border-2 border-slate/30 bg-white px-3.5 py-2 focus:border-ink focus:outline-none">
                    <p class="mt-1 text-xs text-slate">
                        @if($secretSaved)A secret is saved ({{ $secretHint }}). Leave blank to keep it, or paste a new one to replace it.@else The private key. It is stored encrypted and never shown again.@endif
                    </p>
                    @if($secretSaved)<label class="mt-2 inline-flex items-center gap-2 text-sm"><input type="checkbox" name="secret_remove" value="1"> Remove the saved secret</label>@endif
                </div>
            </div>
            <div>
                <label class="inline-flex min-h-11 cursor-pointer items-center gap-2 font-semibold"><input type="checkbox" name="enabled" value="1" class="size-5" @checked($enabled)> Use reCAPTCHA on my forms</label>
                <p class="text-sm text-slate">Untick only for testing. Without it, forms accept any visitor.</p>
            </div>
            <x-button type="submit">Save keys</x-button>
            <p class="text-sm text-slate">Leave both keys empty to use the keys in the server .env file (RECAPTCHA_SITE_KEY / RECAPTCHA_SECRET_KEY).</p>
        </section>
    </form>

    <section class="space-y-3 rounded-2xl bg-paper p-6 shadow-card" aria-labelledby="rc-check">
        <h2 id="rc-check" class="text-xl">Check the secret key</h2>
        <form method="POST" action="{{ route('admin.recaptcha.test') }}">@csrf
            <button type="submit" class="min-h-11 rounded-full border-2 border-ink px-5 font-semibold hover:bg-ink hover:text-cream">Test secret key</button>
        </form>
        <p class="text-sm text-slate">Asks Google whether the saved secret is valid. It cannot check the site key, so after saving, open the website and tick the box once to confirm the widget shows and an enquiry sends.</p>
    </section>
</div>
@endsection
