# Google Sheets lead feed

Every form submission (quiz, quote popup, package quote, ask-a-question, contact) is added as one row to a Google Sheet, with the first-touch and last-touch UTM values and ad click ids (GCLID, GBRAID, WBRAID). Manage it in **Admin > Google Sheets**.

## How it works
- The client creates a Google Sheet and adds a small **Apps Script web app** to it (the script is shown, with a Copy button, on the admin screen). The site POSTs JSON to that web app's URL together with a shared secret key.
- Nothing from Google is stored on the site except the web app URL and the secret, both kept **encrypted** (`sheet_webhook_url`, `sheet_secret` in the settings table; `.env` fallbacks `GOOGLE_SHEET_WEBHOOK_URL`, `GOOGLE_SHEET_SECRET`).
- `EnquiryService` saves the enquiry, then (after the GoHighLevel sync) calls `SyncEnquiryToSheet::dispatchSync`. A failure never fails the enquiry: it is recorded in `enquiries.sheet_status` / `sheet_error` and a delayed queued retry is added. "Send to sheet" on an enquiry page and "Send past enquiries" (25 per click) in the admin screen cover the rest.
- The script finds a row by Enquiry ID, so sending the same enquiry again **updates** its row (no duplicates). It writes every cell as plain text, so nothing a visitor types can run as a formula.
- **One tab per form** (`SheetClient::TABS`): Main Quiz (hero-quiz), Get Quote Popup (modal), Package Quote, Ask a Question, Contact Form; anything else goes to Other Forms; the test row goes to Connection Test. The script creates each tab and its heading row the first time. If the script was deployed before this change, replace it with the current one and redeploy as a new version.
- Columns: see `SheetClient::headers()`. The IP address, user agent and consent wording are deliberately NOT sent.

## Set-up steps (client side, about 5 minutes)
1. Create a blank spreadsheet at sheets.google.com (for example "Go Belgium Leads").
2. **Extensions > Apps Script**. Delete the default code in Code.gs.
3. In the site admin open **Google Sheets**, click **Generate** next to Secret key and copy the key. Click the Copy button above the script and paste the script into Code.gs.
4. On the first line replace `PASTE_YOUR_SECRET_KEY_HERE` with the key. Save (disk icon).
5. **Deploy > New deployment > gear icon > Web app**. Execute as: **Me**. Who has access: **Anyone**. Deploy.
6. Authorise when asked (Authorize access > your account > Advanced > Go to project (unsafe) > Allow). Copy the **Web app URL** (ends in `/exec`).
7. In the admin paste the URL, keep the same secret key, tick **Send enquiries to Google Sheets**, Save.
8. **Send a test row**. A TEST line (with the headings above it) must appear. Delete it, then **Send past enquiries** if wanted.

Changing the script later: Deploy > Manage deployments > pencil > Version: New version > Deploy (the URL stays the same).

## Troubleshooting
- "Wrong secret key": the key in the script differs from the one saved in the admin. Paste the same key in both and redeploy the script (new version).
- "Google answered with error 401/403/404": the web app is not deployed with access "Anyone", or the URL is old. Redeploy and paste the new URL.
- Test row works but real leads do not: check Enquiries > an enquiry > Google Sheet status; the message says why.
- If `APP_KEY` ever changes, the saved URL and secret become unreadable: paste them again.