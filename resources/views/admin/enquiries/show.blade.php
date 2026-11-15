@extends('layouts.admin')
@section('title', 'Enquiry '.$enquiry->id)
@section('heading', 'Enquiry from '.$enquiry->fullName())

@section('content')
@php $labels = $enquiry->answers ? \App\Services\EnquiryService::answerLabels($enquiry->answers) : []; @endphp
<div class="grid gap-6 lg:grid-cols-[1fr_22rem]">
    <div class="space-y-6">
        <section class="rounded-2xl bg-paper p-5 shadow-card" aria-labelledby="d1">
            <h2 id="d1" class="text-xl">Contact and request</h2>
            <dl class="mt-3 grid gap-x-6 gap-y-2 sm:grid-cols-2">
                @foreach([['Name', $enquiry->fullName()], ['Email', $enquiry->email], ['Phone', $enquiry->phone_e164], ['Form', $enquiry->form_name], ['Package', $enquiry->package_name], ['Tour type', $enquiry->tour_type], ['Travel date', $enquiry->travel_date], ['Second quote', $enquiry->second_quote], ['Received', $enquiry->created_at->format('d M Y H:i')]] as [$k, $v])
                    @if($v)<div><dt class="text-sm text-slate">{{ $k }}</dt><dd class="font-semibold break-words">{{ $v }}</dd></div>@endif
                @endforeach
            </dl>
            @if($enquiry->message)<h3 class="mt-4 text-lg">Message</h3><p class="mt-1 whitespace-pre-line">{{ $enquiry->message }}</p>@endif
        </section>

        @if($labels)
            <section class="rounded-2xl bg-paper p-5 shadow-card" aria-labelledby="d2"><h2 id="d2" class="text-xl">Quiz answers</h2>
                <dl class="mt-3 grid gap-2 sm:grid-cols-2">@foreach($labels as $k => $v)<div><dt class="text-sm capitalize text-slate">{{ str_replace('_', ' ', $k) }}</dt><dd class="font-semibold">{{ $v }}</dd></div>@endforeach</dl>
            </section>
        @endif

        <section class="rounded-2xl bg-paper p-5 shadow-card" aria-labelledby="d3"><h2 id="d3" class="text-xl">Consent evidence</h2>
            <p class="mt-2 text-sm text-slate">Keep this for disputes and chargebacks.</p>
            <dl class="mt-3 space-y-2 text-sm">
                <div><dt class="text-slate">Consent given</dt><dd class="font-semibold">{{ $enquiry->consent_given ? 'Yes' : 'No' }} at {{ $enquiry->consent_at->format('d M Y H:i:s T') }}</dd></div>
                <div><dt class="text-slate">Terms version</dt><dd class="font-semibold">{{ $enquiry->terms_version }}</dd></div>
                <div><dt class="text-slate">Exact wording shown</dt><dd class="rounded-lg bg-cream p-3">{{ $enquiry->consent_text }}</dd></div>
                <div><dt class="text-slate">IP address</dt><dd class="font-semibold">{{ $enquiry->ip_address }}</dd></div>
                <div><dt class="text-slate">Browser</dt><dd class="break-words">{{ $enquiry->user_agent }}</dd></div>
                <div><dt class="text-slate">Page</dt><dd class="break-all">{{ $enquiry->page_url }}</dd></div>
            </dl>
        </section>

        <section class="rounded-2xl bg-paper p-5 shadow-card" aria-labelledby="d4"><h2 id="d4" class="text-xl">Where they came from</h2>
            <div class="mt-3 grid gap-6 sm:grid-cols-2">
                @foreach(['First touch' => $enquiry->tracking_first, 'Latest touch' => $enquiry->tracking_last] as $title => $t)
                    <div><h3 class="font-display text-lg">{{ $title }}</h3>
                        @if($t)<dl class="mt-2 space-y-1 text-sm">@foreach($t as $k => $v)@if($v && $k !== 'captured_at')<div><dt class="inline text-slate">{{ $k }}:</dt> <dd class="inline break-all font-semibold">{{ $v }}</dd></div>@endif @endforeach</dl>
                        @else<p class="mt-2 text-sm text-slate">Not recorded (direct visit).</p>@endif
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <form method="POST" action="{{ route('admin.enquiries.update', $enquiry) }}" class="space-y-4 rounded-2xl bg-paper p-5 shadow-card">
            @csrf @method('PUT')
            <h2 class="text-xl">Follow up</h2>
            <x-admin.select name="status" label="Status" :options="\App\Models\Enquiry::STATUSES" :value="$enquiry->status" />
            <x-admin.textarea name="notes" label="Internal notes" :value="$enquiry->notes" :rows="6" help="Only staff see these." />
            <x-button type="submit">Save</x-button>
        </form>

        <section class="rounded-2xl bg-paper p-5 shadow-card"><h2 class="text-xl">GoHighLevel</h2>
            <p class="mt-2">Status: <strong>{{ $enquiry->ghl_status }}</strong></p>
            @if($enquiry->ghl_contact_id)<p class="text-sm text-slate break-all">Contact ID: {{ $enquiry->ghl_contact_id }}</p>@endif
            @if($enquiry->ghl_error)<p class="mt-2 rounded-lg bg-red-50 p-2 text-sm text-red-900">{{ $enquiry->ghl_error }}</p>@endif
            <form method="POST" action="{{ route('admin.enquiries.ghl-retry', $enquiry) }}" class="mt-3">@csrf<x-button type="submit" size="sm" variant="dark">Retry sync</x-button></form>
        </section>

        <form method="POST" action="{{ route('admin.enquiries.destroy', $enquiry) }}" onsubmit="return confirm('Delete this enquiry permanently? Consent evidence will be lost.')">
            @csrf @method('DELETE')<x-button type="submit" variant="outline" size="sm">Delete enquiry</x-button>
        </form>
    </aside>
</div>
@endsection
