@extends('layouts.admin')
@section('title', 'GoHighLevel')
@section('heading', 'GoHighLevel integration')

@section('content')
<div class="max-w-3xl space-y-6">
    <section class="space-y-3 rounded-2xl bg-paper p-6 shadow-card" aria-labelledby="ghl-status">
        <div class="flex flex-wrap items-center gap-3">
            <h2 id="ghl-status" class="sr-only">Status</h2>
            @if($status === 'on')
                <span class="inline-flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800"><span class="size-2 rounded-full bg-green-600"></span>On: enquiries are sent to GoHighLevel</span>
            @elseif($status === 'paused')
                <span class="inline-flex items-center gap-2 rounded-full bg-amber/30 px-3 py-1 text-sm font-semibold"><span class="size-2 rounded-full bg-amber-deep"></span>Paused: credentials saved, nothing is sent</span>
            @else
                <span class="inline-flex items-center gap-2 rounded-full bg-ink/10 px-3 py-1 text-sm font-semibold"><span class="size-2 rounded-full bg-slate"></span>Off: add the Location ID and API token below</span>
            @endif
            <span class="text-sm text-slate">
                @if($source === 'admin')Credentials saved here (the token is stored encrypted).
                @elseif($source === 'env')Using the values from the server's .env file. Saving here overrides them.
                @else No credentials saved yet.@endif
            </span>
        </div>
        <p class="text-sm text-slate">Every form on the site (the quiz, quote requests, questions and the contact form) uses the same pipeline, so once this is On they all send their enquiries to GoHighLevel. Enquiries are always saved here first: if GoHighLevel is off or fails, nothing is lost and the enquiry is never blocked.</p>
    </section>

    <form method="POST" action="{{ route('admin.ghl.update') }}" class="space-y-6">
        @csrf @method('PUT')
        <section class="space-y-4 rounded-2xl bg-paper p-6 shadow-card" aria-labelledby="ghl-conn">
            <h2 id="ghl-conn" class="text-xl">Connection details</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.input name="location_id" label="Location ID" :value="$locationId" autocomplete="off" help="GoHighLevel > Settings > Business Profile (or the Location ID in the sub-account URL)." />
                <div>
                    <label for="a-api_token" class="mb-1 block text-sm font-semibold">API token (Private Integration token)</label>
                    <input id="a-api_token" name="api_token" type="password" autocomplete="new-password" value="" placeholder="{{ $tokenHint ?: 'Paste your token' }}"
                           class="block min-h-11 w-full rounded-xl border-2 border-slate/30 bg-white px-3.5 py-2 focus:border-ink focus:outline-none">
                    <p class="mt-1 text-xs text-slate">
                        @if($tokenHint)A token is saved ({{ $tokenHint }}). Leave blank to keep it, or paste a new one to replace it.@else Create it in GoHighLevel > Settings > Private Integrations. It is shown only once there.@endif
                    </p>
                    @if($tokenSaved)<label class="mt-2 inline-flex items-center gap-2 text-sm"><input type="checkbox" name="token_remove" value="1"> Remove the saved token</label>@endif
                </div>
            </div>
            <div>
                <label class="inline-flex min-h-11 cursor-pointer items-center gap-2 font-semibold"><input type="checkbox" name="send" value="1" class="size-5" @checked($sendOn)> Send enquiries to GoHighLevel</label>
                <p class="text-sm text-slate">Untick to pause the sync without losing your credentials.</p>
            </div>
            <x-button type="submit">Save</x-button>
        </section>
    </form>

    <section class="space-y-4 rounded-2xl bg-paper p-6 shadow-card" aria-labelledby="ghl-check">
        <h2 id="ghl-check" class="text-xl">Check and prepare</h2>
        <div class="grid gap-6 sm:grid-cols-2">
            <form method="POST" action="{{ route('admin.ghl.test') }}">@csrf
                <button type="submit" class="min-h-11 rounded-full border-2 border-ink px-5 font-semibold hover:bg-ink hover:text-cream">Test connection</button>
                <p class="mt-2 text-sm text-slate">Checks the token and Location ID and that the {{ $fieldCount }} custom fields exist. Creates nothing and sends no contact.</p>
            </form>
            <form method="POST" action="{{ route('admin.ghl.setup') }}">@csrf
                <button type="submit" class="min-h-11 rounded-full border-2 border-ink px-5 font-semibold hover:bg-ink hover:text-cream">Create missing custom fields</button>
                <p class="mt-2 text-sm text-slate">Adds the {{ $fieldCount }} fields enquiries use (quiz answers, consent, tracking). Safe to repeat. Do not rename them in GoHighLevel.</p>
            </form>
        </div>
        <p class="text-sm text-slate">Enquiries that arrived while this was off stay in <a href="{{ route('admin.enquiries.index') }}" class="underline">Enquiries</a>; open one and press Retry to send it.</p>
    </section>
</div>
@endsection
