@extends('layouts.admin')
@section('title', 'Google Sheets')
@section('heading', 'Google Sheets')

@section('content')
<div class="max-w-3xl space-y-6">
    <section class="space-y-3 rounded-2xl bg-paper p-6 shadow-card" aria-labelledby="sh-status">
        <div class="flex flex-wrap items-center gap-3">
            <h2 id="sh-status" class="sr-only">Status</h2>
            @if($status === 'on')
                <span class="inline-flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800"><span class="size-2 rounded-full bg-green-600"></span>On: every enquiry is added to your Google Sheet</span>
            @elseif($status === 'paused')
                <span class="inline-flex items-center gap-2 rounded-full bg-amber/30 px-3 py-1 text-sm font-semibold"><span class="size-2 rounded-full bg-amber-deep"></span>Paused: connection saved, nothing is sent</span>
            @else
                <span class="inline-flex items-center gap-2 rounded-full bg-ink/10 px-3 py-1 text-sm font-semibold"><span class="size-2 rounded-full bg-slate"></span>Off: add the web app URL and the secret key below</span>
            @endif
            <span class="text-sm text-slate">{{ $counts['synced'] }} in the sheet · {{ $counts['failed'] }} failed · {{ $counts['skipped'] }} skipped</span>
        </div>
        <p class="text-sm text-slate">Every form on the site (the quiz, quote requests, questions and the contact form) uses the same pipeline, so once this is On they all add one row to your sheet, including the UTM parameters and ad click ids (GCLID, GBRAID, WBRAID) of the first and the last visit. Enquiries are always saved here first: if Google is unreachable, nothing is lost and the visitor never sees an error.</p>
    </section>

    <form method="POST" action="{{ route('admin.sheets.update') }}" class="space-y-6">
        @csrf @method('PUT')
        <section class="space-y-4 rounded-2xl bg-paper p-6 shadow-card" aria-labelledby="sh-conn" x-data="{ secret: '', make() { const a = new Uint8Array(24); crypto.getRandomValues(a); this.secret = Array.from(a, b => 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789'[b % 56]).join(''); } }">
            <h2 id="sh-conn" class="text-xl">Connection</h2>
            <div>
                <label for="a-webhook_url" class="mb-1 block text-sm font-semibold">Web app URL</label>
                <input id="a-webhook_url" name="webhook_url" type="text" autocomplete="off" value="" placeholder="{{ $urlHint ? 'A URL is saved ('.$urlHint.')' : 'https://script.google.com/macros/s/.../exec' }}"
                       class="block min-h-11 w-full rounded-xl border-2 border-slate/30 bg-white px-3.5 py-2 focus:border-ink focus:outline-none">
                <p class="mt-1 text-xs text-slate">@if($urlHint)A URL is saved and stored encrypted. Leave blank to keep it, or paste a new one to replace it.@else Step 6 of the instructions below gives you this URL.@endif</p>
                @if($urlSaved)<label class="mt-2 inline-flex items-center gap-2 text-sm"><input type="checkbox" name="url_remove" value="1"> Remove the saved URL</label>@endif
            </div>
            <div>
                <label for="a-secret" class="mb-1 block text-sm font-semibold">Secret key</label>
                <div class="flex flex-wrap gap-2">
                    <input id="a-secret" name="secret" type="text" autocomplete="off" x-model="secret" placeholder="{{ $secretHint ? 'A key is saved ('.$secretHint.')' : 'Click Generate, then copy it into the script' }}"
                           class="block min-h-11 min-w-0 flex-1 rounded-xl border-2 border-slate/30 bg-white px-3.5 py-2 font-mono text-sm focus:border-ink focus:outline-none">
                    <button type="button" @click="make()" class="min-h-11 rounded-full border-2 border-ink px-5 font-semibold hover:bg-ink hover:text-cream">Generate</button>
                </div>
                <p class="mt-1 text-xs text-slate">The same key must be pasted into the script (step 4). It stops anyone else from writing to your sheet. It is stored encrypted and never shown again, only its last 4 characters. @if($secretHint)Leave blank to keep the saved key.@endif</p>
            </div>
            <div>
                <label class="inline-flex min-h-11 cursor-pointer items-center gap-2 font-semibold"><input type="checkbox" name="send" value="1" class="size-5" @checked($sendOn)> Send enquiries to Google Sheets</label>
                <p class="text-sm text-slate">Untick to pause the sync without losing your connection.</p>
            </div>
            <x-button type="submit">Save</x-button>
        </section>
    </form>

    <section class="space-y-4 rounded-2xl bg-paper p-6 shadow-card" aria-labelledby="sh-check">
        <h2 id="sh-check" class="text-xl">Check and catch up</h2>
        <div class="grid gap-6 sm:grid-cols-2">
            <form method="POST" action="{{ route('admin.sheets.test') }}">@csrf
                <button type="submit" class="min-h-11 rounded-full border-2 border-ink px-5 font-semibold hover:bg-ink hover:text-cream">Send a test row</button>
                <p class="mt-2 text-sm text-slate">Adds one line marked TEST to the sheet so you can see the connection works. Delete the line afterwards.</p>
            </form>
            <form method="POST" action="{{ route('admin.sheets.backfill') }}">@csrf
                <button type="submit" class="min-h-11 rounded-full border-2 border-ink px-5 font-semibold hover:bg-ink hover:text-cream">Send past enquiries</button>
                <p class="mt-2 text-sm text-slate">{{ $notSent }} {{ $notSent === 1 ? 'enquiry is' : 'enquiries are' }} not in the sheet yet (received before this was set up, or failed). Sends 25 at a time, oldest first: press again until none are left.</p>
            </form>
        </div>
        <p class="text-sm text-slate">An enquiry that failed can also be re-sent from its own page in <a href="{{ route('admin.enquiries.index') }}" class="underline">Enquiries</a> (Send to sheet). Sending the same enquiry twice updates its row, it never adds a duplicate.</p>
    </section>

    <section class="space-y-3 rounded-2xl bg-paper p-6 shadow-card" aria-labelledby="sh-how">
        <h2 id="sh-how" class="text-xl">Set up your Google Sheet (about 5 minutes, once)</h2>
        <ol class="space-y-2 text-sm text-slate" style="list-style-type: decimal; padding-left: 1.25rem">
            <li>Go to <strong>sheets.google.com</strong> and create a blank spreadsheet. Name it, for example, "Go Belgium Leads". Leave it empty: the script adds the column headings itself.</li>
            <li>In the sheet menu choose <strong>Extensions &gt; Apps Script</strong>. A new tab opens with a file called Code.gs.</li>
            <li>Delete everything in Code.gs and paste the script below (use the Copy button).</li>
            <li>On the first line, replace <code>PASTE_YOUR_SECRET_KEY_HERE</code> with the Secret key from this page (click Generate, copy it, paste it between the quotes). Click the disk icon to save.</li>
            <li>Click <strong>Deploy &gt; New deployment</strong>. Click the gear icon next to "Select type" and choose <strong>Web app</strong>. Set <strong>Execute as: Me</strong> and <strong>Who has access: Anyone</strong>, then click <strong>Deploy</strong>.</li>
            <li>Google asks you to authorise the script: click <strong>Authorize access</strong>, choose your Google account, then <strong>Advanced &gt; Go to (project name) (unsafe) &gt; Allow</strong>. This is normal for your own scripts. Copy the <strong>Web app URL</strong> that ends in <code>/exec</code>.</li>
            <li>Come back here, paste the URL in "Web app URL", make sure the Secret key matches, tick "Send enquiries to Google Sheets" and click <strong>Save</strong>.</li>
            <li>Click <strong>Send a test row</strong>. A line marked TEST should appear in your sheet (with the headings above it). Delete that line, then click <strong>Send past enquiries</strong> if you want the older leads too.</li>
        </ol>
        <p class="text-sm text-slate"><strong>Already set up before the separate tabs?</strong> Replace the script in Code.gs with the one below (keep your secret key on the first line), then redeploy it as a new version (see the next line). Until you do, everything still goes to one tab.</p>
        <p class="text-sm text-slate"><strong>If you change the script later,</strong> use Deploy &gt; Manage deployments &gt; pencil icon &gt; Version: New version &gt; Deploy. The URL stays the same.</p>

        <div x-data="{ copied: false }">
            <div class="mb-1 flex items-center justify-between">
                <label for="sh-script" class="text-sm font-semibold">The script (Code.gs)</label>
                <button type="button" class="min-h-9 rounded-full border-2 border-ink px-4 text-sm font-semibold hover:bg-ink hover:text-cream"
                        @click="navigator.clipboard.writeText($refs.script.value).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"><span x-text="copied ? 'Copied' : 'Copy'">Copy</span></button>
            </div>
@verbatim
<textarea id="sh-script" x-ref="script" readonly rows="16" spellcheck="false" class="block w-full rounded-xl border-2 border-slate/30 bg-white p-3 font-mono text-xs leading-relaxed focus:border-ink focus:outline-none">const SECRET = 'PASTE_YOUR_SECRET_KEY_HERE';
const DEFAULT_TAB = 'Other Forms';

function doPost(e) {
  const lock = LockService.getScriptLock();
  try {
    const data = JSON.parse(e.postData.contents);
    if (data.secret !== SECRET) return out({ ok: false, error: 'Wrong secret key' });
    lock.waitLock(20000);

    const ss = SpreadsheetApp.getActiveSpreadsheet();
    // One tab per form: the site tells the script which tab to use, and the tab is created the first time.
    const tabName = String(data.tab || DEFAULT_TAB).replace(/[\[\]\*\/\\\?:]/g, ' ').slice(0, 90);
    const sheet = ss.getSheetByName(tabName) || ss.insertSheet(tabName);
    const headers = data.headers;
    if (sheet.getLastRow() === 0) {
      sheet.getRange(1, 1, 1, headers.length).setNumberFormat('@').setValues([headers]).setFontWeight('bold');
      sheet.setFrozenRows(1);
    }

    // The same enquiry sent twice (a retry) updates its own row instead of adding a duplicate.
    const id = String(data.row[headers[0]]);
    let target = sheet.getLastRow() + 1;
    if (sheet.getLastRow() > 1) {
      const hit = sheet.getRange(2, 1, sheet.getLastRow() - 1, 1).createTextFinder(id).matchEntireCell(true).findNext();
      if (hit) target = hit.getRow();
    }

    const values = headers.map(function (h) { return data.row[h] == null ? '' : String(data.row[h]); });
    // Plain text format: nothing a visitor types can ever run as a formula.
    sheet.getRange(target, 1, 1, values.length).setNumberFormat('@').setValues([values]);
    return out({ ok: true, row: target });
  } catch (err) {
    return out({ ok: false, error: String(err) });
  } finally {
    try { lock.releaseLock(); } catch (x) {}
  }
}

function doGet() {
  return out({ ok: true, message: 'The Go Belgium leads webhook is live.' });
}

function out(obj) {
  return ContentService.createTextOutput(JSON.stringify(obj)).setMimeType(ContentService.MimeType.JSON);
}</textarea>
@endverbatim
        </div>
    </section>

    <section class="rounded-2xl bg-paper p-6 shadow-card">
        <h2 class="text-xl">Which tab each form goes to</h2>
        <ul class="mt-2 space-y-1 text-sm text-slate">
            @foreach($tabs as $form => $tab)<li><strong>{{ $tab }}</strong>: {{ $formLabels[$form] ?? $form }}</li>@endforeach
        </ul>
        <p class="mt-2 text-sm text-slate">The tabs are created automatically the first time a form is used, each with the same column headings. The test row goes to a tab called {{ $testTab }}; you can delete that tab afterwards.</p>
        <h2 class="mt-6 text-xl">What goes into each row</h2>
        <p class="mt-2 text-sm text-slate">{{ implode(' · ', $columns) }}.</p>
        <p class="mt-2 text-sm text-slate">The visitor's IP address, browser details and the consent wording are not sent to the sheet (they stay in Enquiries). Anyone you share the sheet with can read the leads, so share it carefully.</p>
    </section>
</div>
@endsection
