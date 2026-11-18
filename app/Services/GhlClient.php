<?php

namespace App\Services;

use App\Models\Enquiry;
use App\Models\Setting;
use App\Services\Ghl\GhlFields;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

/**
 * GoHighLevel API v2 client (Private Integration Token). OFF whenever the token
 * or location id is empty. Never logs the token.
 */
class GhlClient
{
    /** Credentials saved in Admin > GoHighLevel win; the .env values are the fallback. */
    public function token(): ?string
    {
        $saved = Setting::get('ghl_api_token');
        if ($saved) {
            try {
                return Crypt::decryptString($saved);
            } catch (\Throwable $e) {
                return null; // unreadable (APP_KEY changed): treated as "no token", never a crash
            }
        }

        return config('gotour.ghl.token') ?: null;
    }

    public function locationId(): ?string
    {
        return Setting::get('ghl_location_id') ?: (config('gotour.ghl.location_id') ?: null);
    }

    /** True when the sync is switched off on purpose (the "Send enquiries to GoHighLevel" box is unticked). */
    public function paused(): bool
    {
        return Setting::get('ghl_enabled') === '0';
    }

    public function enabled(): bool
    {
        return filled($this->token()) && filled($this->locationId()) && ! $this->paused();
    }

    /** Where the active credentials come from: 'admin', 'env' or 'none'. */
    public function source(): string
    {
        if (Setting::get('ghl_api_token') || Setting::get('ghl_location_id')) {
            return 'admin';
        }

        return (filled(config('gotour.ghl.token')) || filled(config('gotour.ghl.location_id'))) ? 'env' : 'none';
    }

    /** Create the custom fields this site uses that are missing in GoHighLevel. Returns counts and any errors. */
    public function setupFields(): array
    {
        $existing = $this->fieldIds(fresh: true);
        $created = $skipped = 0;
        $failed = [];
        foreach (GhlFields::definitions() as $name => $type) {
            if (isset($existing[$name])) {
                $skipped++;

                continue;
            }
            try {
                $this->createField($name, $type);
                $created++;
            } catch (\Throwable $e) {
                $failed[] = $name.': '.mb_substr($e->getMessage(), 0, 120);
            }
        }
        $this->fieldIds(fresh: true);

        return ['created' => $created, 'existing' => $skipped, 'failed' => $failed, 'total' => count(GhlFields::definitions())];
    }

    /** How many of the site's custom fields already exist in GoHighLevel. */
    public function fieldStatus(): array
    {
        $existing = $this->fieldIds(fresh: true);
        $missing = array_values(array_filter(array_keys(GhlFields::definitions()), fn ($n) => ! isset($existing[$n])));

        return ['total' => count(GhlFields::definitions()), 'missing' => $missing];
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl(config('gotour.ghl.base_url'))
            ->withToken((string) $this->token())
            ->withHeaders(['Version' => config('gotour.ghl.version')])
            ->acceptJson()->asJson()->timeout(20);
    }

    private function loc(): string
    {
        return (string) $this->locationId();
    }

    /** name => id for existing contact custom fields. Cached for an hour. */
    public function fieldIds(bool $fresh = false): array
    {
        $key = 'ghl.field_ids.'.$this->loc();
        if ($fresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, 3600, function () {
            $res = $this->http()->get('/locations/'.$this->loc().'/customFields', ['model' => 'contact'])->throw();

            return collect($res->json('customFields', []))->pluck('id', 'name')->all();
        });
    }

    public function createField(string $name, string $dataType): ?string
    {
        $res = $this->http()->post('/locations/'.$this->loc().'/customFields', [
            'name' => $name, 'dataType' => $dataType, 'model' => 'contact',
        ])->throw();

        return $res->json('customField.id');
    }

    public function checkConnection(): array
    {
        $res = $this->http()->get('/locations/'.$this->loc());

        // GoHighLevel explains refusals in the body ("Invalid JWT", "not authorized for this scope", ...): pass that on.
        $said = $res->json('message') ?? $res->json('error') ?? $res->json('msg');

        return ['ok' => $res->successful(), 'status' => $res->status(), 'name' => $res->json('location.name') ?? $res->json('name'), 'said' => is_array($said) ? implode('; ', $said) : (string) $said];
    }

    /** Build the contact payload for an enquiry. Public so tests can inspect it. */
    public function payload(Enquiry $e, array $ids, array $existingFirst = []): array
    {
        $values = [
            'Preferred Travel Date' => $e->travel_date,
            'Second Quote Preference' => $e->second_quote,
            'Special Request' => $e->message,
            'Package' => $e->package_name,
            'Tour Type' => $e->tour_type,
            'Form Name' => $e->form_name,
            'Consent Given' => $e->consent_given ? 'Yes' : 'No',
            'Consent Timestamp' => $e->consent_at?->toIso8601String(),
            'Consent Text Version' => $e->terms_version,
        ];

        $answers = $e->answers ?? [];
        $labels = $answers ? EnquiryService::answerLabels($answers) : [];
        foreach (GhlFields::quizFieldMap() as $field => $stepKey) {
            $values[$field] = $labels[$stepKey] ?? null;
        }
        $values['Quiz Answers'] = $labels
            ? collect($labels)->map(fn ($v, $k) => str_replace('_', ' ', $k).': '.$v)->implode("\n")
            : null;

        foreach (['First' => $e->tracking_first, 'Last' => $e->tracking_last] as $touch => $data) {
            foreach (GhlFields::trackingLabelMap() as $key => $label) {
                $name = "$label ($touch)";
                // Never erase first-touch data already stored on a returning contact.
                if ($touch === 'First' && isset($existingFirst[$name]) && $existingFirst[$name] !== '') {
                    continue;
                }
                $values[$name] = $data[$key] ?? null;
            }
        }

        $customFields = [];
        foreach ($values as $name => $value) {
            if ($value !== null && $value !== '' && isset($ids[$name])) {
                $customFields[] = ['id' => $ids[$name], 'field_value' => (string) $value];
            }
        }

        $tags = array_values(array_filter([
            Setting::get('brand_name', config('app.name')),
            'website-enquiry',
            'form: '.$e->form_name,
            $e->package_name ? 'package: '.$e->package_name : null,
            $e->tour_type ? 'tour: '.$e->tour_type : null,
            ($e->tracking_last['utm_source'] ?? null) ? 'source: '.$e->tracking_last['utm_source'] : null,
        ]));

        return [
            'locationId' => $this->loc(),
            'firstName' => $e->first_name,
            'lastName' => $e->last_name,
            'email' => $e->email,
            'phone' => $e->phone_e164,
            'source' => 'Website: '.$e->form_name,
            'tags' => $tags,
            'customFields' => $customFields,
        ];
    }

    /** Upsert the contact. Returns the GHL contact id. Throws on failure (the job retries). */
    public function sync(Enquiry $e): string
    {
        $ids = $this->fieldIds();
        $existingFirst = $this->existingFirstTouch($e->email, $ids);

        $res = $this->http()->post('/contacts/upsert', $this->payload($e, $ids, $existingFirst))->throw();

        return (string) $res->json('contact.id');
    }

    /** Current values of the "(First)" fields on an existing contact, keyed by field name. */
    private function existingFirstTouch(string $email, array $ids): array
    {
        try {
            $res = $this->http()->get('/contacts/search/duplicate', ['locationId' => $this->loc(), 'email' => $email]);
            if (! $res->successful() || ! $res->json('contact.id')) {
                return [];
            }
            $byId = array_flip($ids);
            $out = [];
            foreach ($res->json('contact.customFields', []) as $cf) {
                $name = $byId[$cf['id']] ?? null;
                if ($name && str_ends_with($name, '(First)')) {
                    $out[$name] = is_scalar($cf['value'] ?? null) ? (string) $cf['value'] : '';
                }
            }

            return $out;
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function upsertRaw(array $body): array
    {
        return $this->http()->post('/contacts/upsert', $body)->throw()->json();
    }

    public function deleteContact(string $id): bool
    {
        return $this->http()->delete('/contacts/'.$id)->successful();
    }
}
