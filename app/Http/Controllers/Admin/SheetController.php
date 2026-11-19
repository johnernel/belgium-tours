<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncEnquiryToSheet;
use App\Models\Enquiry;
use App\Models\Setting;
use App\Services\SheetClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/**
 * Admin > Google Sheets: the Apps Script web app URL and a shared secret (both stored encrypted, never shown again), an on/off switch,
 * a test row and a button that sends past enquiries that never reached the sheet. Every form shares one pipeline, so saving a working
 * connection here sends every form submission, with its UTM values, to the sheet.
 */
class SheetController extends Controller
{
    /** Past enquiries sent per click of "Send past enquiries" (kept small so the request never times out). */
    private const BACKFILL_BATCH = 25;

    public function edit(SheetClient $sheet)
    {
        $url = $sheet->url();
        $secret = $sheet->secret();

        return view('admin.sheets', [
            'status' => $sheet->enabled() ? 'on' : ($sheet->paused() ? 'paused' : 'off'),
            'urlHint' => $url ? '…'.substr($url, -12) : null,
            'secretHint' => $secret ? '••••••••'.substr($secret, -4) : null,
            'urlSaved' => filled(Setting::get('sheet_webhook_url')),
            'sendOn' => ! $sheet->paused(),
            'counts' => collect(['synced', 'failed', 'pending', 'skipped'])->mapWithKeys(fn ($s) => [$s => Enquiry::where('sheet_status', $s)->count()])->all(),
            'notSent' => Enquiry::where(fn ($q) => $q->whereNull('sheet_status')->orWhereIn('sheet_status', ['failed', 'pending', 'skipped']))->count(),
            'columns' => SheetClient::headers(),
            'tabs' => SheetClient::TABS,
            'testTab' => SheetClient::TEST_TAB,
            'formLabels' => ['hero-quiz' => 'the main quiz form (home, tour type, destination and other pages)', 'modal' => 'the Get Quote popup form', 'package-quote' => 'the quote form on a package page', 'question' => 'the Ask a Question form', 'contact' => 'the Contact page form'],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'webhook_url' => ['nullable', 'string', 'max:300', 'regex:~^https://script\.google\.com/(a/macros/[A-Za-z0-9.\-]+/)?macros/s/[A-Za-z0-9_\-]+/exec$~'],
            'secret' => ['nullable', 'string', 'min:12', 'max:100', 'regex:/^[A-Za-z0-9_\-]+$/'],
        ], [
            'webhook_url.regex' => 'That does not look like a Google Apps Script web app URL. It starts with https://script.google.com/macros/s/ and ends with /exec.',
            'secret.min' => 'Use at least 12 characters for the secret key.',
            'secret.regex' => 'The secret key can only contain letters, numbers, dashes and underscores.',
        ]);

        if ($request->boolean('url_remove')) {
            Setting::put('sheet_webhook_url', null);
        } elseif (filled($data['webhook_url'] ?? null)) {
            Setting::put('sheet_webhook_url', Crypt::encryptString(trim($data['webhook_url'])));
        }
        if (filled($data['secret'] ?? null)) {
            Setting::put('sheet_secret', Crypt::encryptString(trim($data['secret'])));
        }
        // A blank box keeps what is saved.

        Setting::put('sheet_enabled', $request->boolean('send') ? '1' : '0');

        return back()->with('status', 'Google Sheets settings saved.');
    }

    public function test(SheetClient $sheet)
    {
        if (blank($sheet->url()) || blank($sheet->secret())) {
            return back()->withErrors(['sheet' => 'Save both the web app URL and the secret key first.']);
        }

        try {
            $sheet->sendTest();
        } catch (\Throwable $e) {
            return back()->withErrors(['sheet' => 'Test failed. '.mb_substr($e->getMessage(), 0, 260)]);
        }

        return back()->with('status', 'Test row sent. Open your Google Sheet: a "TEST Row (safe to delete)" line should be there.');
    }

    /** Send enquiries that are not in the sheet yet (never sent, failed, pending or received while the sync was off), oldest first. */
    public function backfill(SheetClient $sheet)
    {
        if (! $sheet->enabled()) {
            return back()->withErrors(['sheet' => 'Turn Google Sheets on and save a working connection first.']);
        }

        $todo = Enquiry::where(fn ($q) => $q->whereNull('sheet_status')->orWhereIn('sheet_status', ['failed', 'pending', 'skipped']))
            ->orderBy('id')->limit(self::BACKFILL_BATCH)->get();

        $sent = 0;
        $firstError = null;
        foreach ($todo as $enquiry) {
            try {
                SyncEnquiryToSheet::dispatchSync($enquiry->id);
            } catch (\Throwable $e) {
                $firstError = $firstError ?: $e->getMessage();
                break; // the connection is failing: stop instead of waiting on every row
            }
            $sent++;
        }

        $left = Enquiry::where(fn ($q) => $q->whereNull('sheet_status')->orWhereIn('sheet_status', ['failed', 'pending', 'skipped']))->count();

        if ($firstError) {
            return back()->withErrors(['sheet' => "Sent $sent, then it stopped: ".mb_substr($firstError, 0, 220)]);
        }

        return back()->with('status', "Sent $sent past enquir".($sent === 1 ? 'y' : 'ies').' to the sheet.'.($left ? " $left still to send: press the button again." : ' Everything is in the sheet.'));
    }
}
