<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

/** Server-side reCAPTCHA v2 verification. Fails CLOSED: any problem means "not human". */
class Recaptcha
{
    public static function siteKey(): ?string
    {
        return Setting::get('recaptcha_site_key') ?: config('gotour.recaptcha.site_key');
    }

    public static function secret(): ?string
    {
        return Setting::get('recaptcha_secret_key') ?: config('gotour.recaptcha.secret_key');
    }

    public function verify(?string $response, ?string $ip = null): bool
    {
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
