<?php

namespace App\Services;

use App\Models\Enquiry;
use App\Models\Setting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

/**
 * Sends every enquiry (with its UTM and click-id values) to a Google Sheet through a small Google Apps Script "web app" that the
 * client creates inside their own spreadsheet (see Admin > Google Sheets and docs/reference/GOOGLE_SHEETS.md). No Google login or
 * API key is stored here: only the web app URL and a shared secret, both kept encrypted. The script appends one row per enquiry and
 * updates the same row if the same enquiry is sent again (retries never create duplicates).
 */
class SheetClient
{
    /** Column headers, in order. The script creates this header row on the first send. */
    public static function headers(): array
    {
        return [
            'Enquiry ID', 'Received', 'Form', 'First name', 'Last name', 'Email', 'Phone', 'Country', 'Travel date', 'Package', 'Tour type',
            'Quiz answers', 'Message',
            'UTM source (last)', 'UTM medium (last)', 'UTM campaign (last)', 'UTM term (last)', 'UTM content (last)', 'GCLID (last)', 'GBRAID (last)', 'WBRAID (last)',
            'UTM source (first)', 'UTM medium (first)', 'UTM campaign (first)', 'UTM term (first)', 'UTM content (first)', 'GCLID (first)',
            'Page URL', 'Consent given', 'Consent at', 'Terms version',
        ];
    }

    public function url(): ?string
    {
        return $this->decrypt(Setting::get('sheet_webhook_url')) ?: (config('gotour.sheet.url') ?: null);
    }

    public function secret(): ?string
    {
        return $this->decrypt(Setting::get('sheet_secret')) ?: (config('gotour.sheet.secret') ?: null);
    }

    /** True when the sync is switched off on purpose (the "Send enquiries to Google Sheets" box is unticked). */
    public function paused(): bool
    {
        return Setting::get('sheet_enabled') === '0';
    }

    public function enabled(): bool
    {
        return filled($this->url()) && filled($this->secret()) && ! $this->paused();
    }

    private function decrypt(?string $stored): ?string
    {
        if (blank($stored)) {
            return null;
        }
        try {
            return Crypt::decryptString($stored);
        } catch (\Throwable $e) {
            return null; // unreadable (APP_KEY changed): treated as "not set", never a crash
        }
    }

    /** One enquiry as header => value. Public so tests can inspect it. IP address, user agent and the consent wording are NOT sent. */
    public function row(Enquiry $e): array
    {
        $labels = ($e->answers ?? []) ? EnquiryService::answerLabels($e->answers) : [];
        $quiz = $labels ? collect($labels)->map(fn ($v, $k) => str_replace('_', ' ', $k).': '.$v)->implode("\n") : '';
        $last = $e->tracking_last ?? [];
        $first = $e->tracking_first ?? [];

        $values = [
            'Enquiry ID' => $e->id,
            'Received' => $e->created_at?->format('Y-m-d H:i:s'),
            'Form' => $e->form_name,
            'First name' => $e->first_name,
            'Last name' => $e->last_name,
            'Email' => $e->email,
            'Phone' => $e->phone_e164,
            'Country' => $e->country_code,
            'Travel date' => $e->travel_date,
            'Package' => $e->package_name,
            'Tour type' => $e->tour_type,
            'Quiz answers' => $quiz,
            'Message' => $e->message,
            'UTM source (last)' => $last['utm_source'] ?? '',
            'UTM medium (last)' => $last['utm_medium'] ?? '',
            'UTM campaign (last)' => $last['utm_campaign'] ?? '',
            'UTM term (last)' => $last['utm_term'] ?? '',
            'UTM content (last)' => $last['utm_content'] ?? '',
            'GCLID (last)' => $last['gclid'] ?? '',
            'GBRAID (last)' => $last['gbraid'] ?? '',
            'WBRAID (last)' => $last['wbraid'] ?? '',
            'UTM source (first)' => $first['utm_source'] ?? '',
            'UTM medium (first)' => $first['utm_medium'] ?? '',
            'UTM campaign (first)' => $first['utm_campaign'] ?? '',
            'UTM term (first)' => $first['utm_term'] ?? '',
            'UTM content (first)' => $first['utm_content'] ?? '',
            'GCLID (first)' => $first['gclid'] ?? '',
            'Page URL' => $e->page_url,
            'Consent given' => $e->consent_given ? 'Yes' : 'No',
            'Consent at' => $e->consent_at?->format('Y-m-d H:i:s'),
            'Terms version' => $e->terms_version,
        ];

        return array_map(fn ($v) => $v === null ? '' : (string) $v, $values);
    }

    /** Append (or update) the enquiry's row. Throws with a readable message on failure (the job retries). */
    public function send(Enquiry $e): void
    {
        $this->post($this->row($e));
    }

    /** A clearly labelled test row, so the client can see the connection works. */
    public function sendTest(): void
    {
        $row = array_fill_keys(self::headers(), '');
        $row['Enquiry ID'] = 'TEST-'.now()->format('YmdHis');
        $row['Received'] = now()->format('Y-m-d H:i:s');
        $row['Form'] = 'test';
        $row['First name'] = 'TEST';
        $row['Last name'] = 'Row (safe to delete)';
        $row['Message'] = 'Sent from Admin > Google Sheets to check the connection.';
        $row['UTM source (last)'] = 'test';
        $this->post($row);
    }

    private function post(array $row): void
    {
        $res = $this->http()->post((string) $this->url(), ['secret' => (string) $this->secret(), 'headers' => self::headers(), 'row' => $row]);

        if (! $res->successful()) {
            throw new \RuntimeException('Google answered with error '.$res->status().'. Check that the web app is deployed with access set to "Anyone".');
        }
        if ($res->json('ok') !== true) {
            $said = $res->json('error');
            throw new \RuntimeException($said ? 'The Google Sheet script says: '.mb_substr((string) $said, 0, 200) : 'The Google Sheet script did not answer as expected. Check the web app URL ends with /exec and the script was deployed as a web app.');
        }
    }

    private function http(): PendingRequest
    {
        // Apps Script answers a POST with a redirect to the result: the default redirect handling follows it.
        return Http::acceptJson()->asJson()->timeout(10);
    }
}
