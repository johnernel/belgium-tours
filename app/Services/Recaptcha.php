<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

/** Server-side reCAPTCHA v2 verification. Fails CLOSED: any problem means "not human". */
class Recaptcha
{
    public static function siteKey(): ?string
    {
        return Setting::get('recaptcha_site_key') ?: config('gotour.recaptcha.site_key');
    }

    /** The secret saved in Admin > reCAPTCHA is stored encrypted ("enc:" prefix). A plain value saved earlier in Settings still works. */
    public static function secret(): ?string
    {
        $saved = Setting::get('recaptcha_secret_key');
        if ($saved) {
            if (str_starts_with((string) $saved, 'enc:')) {
                try {
                    return Crypt::decryptString(substr((string) $saved, 4));
                } catch (\Throwable $e) {
                    return null; // unreadable (APP_KEY changed): treated as "no secret", so forms fail closed
                }
            }

            return (string) $saved;
        }

        return config('gotour.recaptcha.secret_key') ?: null;
    }

    /** The "Use reCAPTCHA on my forms" switch. On unless it was switched off in Admin > reCAPTCHA. */
    public static function enabled(): bool
    {
        return Setting::get('recaptcha_enabled') !== '0';
    }

    /** Where the active keys come from: 'admin', 'env' or 'none'. */
    public static function source(): string
    {
        if (Setting::get('recaptcha_site_key') || Setting::get('recaptcha_secret_key')) {
            return 'admin';
        }

        return (config('gotour.recaptcha.site_key') || config('gotour.recaptcha.secret_key')) ? 'env' : 'none';
    }

    /**
     * Ask Google whether a secret is valid, without a real visitor token. Google answers "invalid-input-secret" for a
     * wrong secret and "invalid-input-response" (the dummy token) for a right one.
     *
     * @return array{ok: bool|null, message: string}
     */
    public function checkSecret(?string $secret): array
    {
        if (! $secret) {
            return ['ok' => false, 'message' => 'No secret key is saved yet.'];
        }
        try {
            $res = Http::asForm()->timeout(8)->post(config('gotour.recaptcha.verify_url'), ['secret' => $secret, 'response' => 'admin-key-check']);
        } catch (\Throwable $e) {
            return ['ok' => null, 'message' => 'Could not reach Google to check the key. Try again in a moment.'];
        }
        $codes = (array) $res->json('error-codes', []);
        if (in_array('invalid-input-secret', $codes, true)) {
            return ['ok' => false, 'message' => 'Google says this secret key is not valid. Copy it again from the reCAPTCHA admin console (the Secret key, not the Site key).'];
        }
        if (! $res->successful()) {
            return ['ok' => null, 'message' => 'Google answered with error '.$res->status().'. Try again in a moment.'];
        }

        return ['ok' => true, 'message' => 'Google accepts this secret key.'];
    }

    public function verify(?string $response, ?string $ip = null): bool
    {
        if (! self::enabled()) {
            return true; // switched off in Admin > reCAPTCHA (the honeypot and the rate limit still apply)
        }
        $secret = self::secret();

        if (! $secret || ! $response) {
            return false;
        }

        try {
            $res = Http::asForm()->timeout(8)->post(config('gotour.recaptcha.verify_url'), [
                'secret' => $secret,
                'response' => $response,
                'remoteip' => $ip,
            ]);

            return $res->successful() && (bool) $res->json('success');
        } catch (\Throwable $e) {
            report($e);

            return false; // Google unreachable: fail closed
        }
    }
}
