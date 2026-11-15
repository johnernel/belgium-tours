<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncEnquiryToGhl;
use App\Models\Enquiry;
use App\Services\GhlClient;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnquiryController extends Controller
{
    private function filtered(Request $request)
    {
        return Enquiry::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $s = '%'.$request->query('q').'%';
                $q->where(fn ($w) => $w->where('first_name', 'like', $s)->orWhere('last_name', 'like', $s)
                    ->orWhere('email', 'like', $s)->orWhere('phone_e164', 'like', $s)->orWhere('package_name', 'like', $s));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('source'), fn ($q) => $q->where('tracking_last->utm_source', $request->query('source')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->query('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->query('to')))
            ->latest();
    }

    public function index(Request $request)
    {
        $sources = Enquiry::query()->whereNotNull('tracking_last')->latest()->take(500)->get()
            ->map(fn ($e) => $e->tracking_last['utm_source'] ?? null)->filter()->unique()->values();

        return view('admin.enquiries.index', [
            'enquiries' => $this->filtered($request)->paginate(20)->withQueryString(),
            'sources' => $sources,
        ]);
    }

    public function show(Enquiry $enquiry)
    {
        return view('admin.enquiries.show', ['enquiry' => $enquiry]);
    }

    public function update(Request $request, Enquiry $enquiry)
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(Enquiry::STATUSES))],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
        $enquiry->update($data);

        return back()->with('status', 'Enquiry updated.');
    }

    public function destroy(Enquiry $enquiry)
    {
        $enquiry->delete();

        return redirect()->route('admin.enquiries.index')->with('status', 'Enquiry deleted.');
    }

    public function retryGhl(Enquiry $enquiry, GhlClient $ghl)
    {
        if (! $ghl->enabled()) {
            return back()->withErrors(['ghl' => 'GoHighLevel is off. Add GHL_API_TOKEN and GHL_LOCATION_ID to .env first.']);
        }

        $enquiry->update(['ghl_status' => 'pending', 'ghl_error' => null]);
        try {
            SyncEnquiryToGhl::dispatch($enquiry->id);
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('status', 'GoHighLevel sync '.($enquiry->fresh()->ghl_status === 'synced' ? 'completed.' : 'queued. Check the status below.'));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = $this->filtered($request);

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Received', 'Form', 'Status', 'First name', 'Last name', 'Email', 'Phone', 'Package', 'Tour type', 'Travel date', 'Second quote', 'Message',
                'Quiz answers', 'UTM source (last)', 'UTM medium (last)', 'UTM campaign (last)', 'GCLID (last)', 'UTM source (first)', 'GCLID (first)', 'Consent at', 'Terms version', 'IP', 'Page URL', 'GHL status']);
            $query->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $e) {
                    fputcsv($out, array_map(fn ($v) => $this->csvSafe($v), [
                        $e->id, $e->created_at, $e->form_name, $e->status, $e->first_name, $e->last_name, $e->email, $e->phone_e164,
                        $e->package_name, $e->tour_type, $e->travel_date, $e->second_quote, $e->message,
                        $e->answers ? json_encode($e->answers) : '',
                        $e->tracking_last['utm_source'] ?? '', $e->tracking_last['utm_medium'] ?? '', $e->tracking_last['utm_campaign'] ?? '', $e->tracking_last['gclid'] ?? '',
                        $e->tracking_first['utm_source'] ?? '', $e->tracking_first['gclid'] ?? '',
                        $e->consent_at, $e->terms_version, $e->ip_address, $e->page_url, $e->ghl_status,
                    ]));
                }
            });
            fclose($out);
        }, 'enquiries-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    /** Stop spreadsheet formula injection from visitor-supplied text. */
    private function csvSafe(mixed $v): mixed
    {
        return is_string($v) && $v !== '' && str_contains('=+-@', $v[0]) ? "'".$v : $v;
    }
}
