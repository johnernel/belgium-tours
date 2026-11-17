<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/**
 * Admin > reCAPTCHA: the site key, the secret key (stored encrypted), the "use reCAPTCHA on my forms" switch and a check of
 * the secret with Google. The switch applies to every form at once, because they all share one enquiry pipeline.
 */
class RecaptchaController extends Controller
{
    public function edit()
    {
        $secret = Recaptcha::secret();

        return view('admin.recaptcha', [
            'enabled' => Recaptcha::enabled(),
            'siteKey' => Recaptcha::siteKey(),
            'secretHint' => $secret ? '••••••••'.substr($secret, -4) : null,
            'secretSaved' => filled(Setting::get('recaptcha_secret_key')),
            'source' => Recaptcha::source(),
            'complete' => filled(Recaptcha::siteKey()) && filled($secret),
            'testKeys' => Recaptcha::siteKey() === '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_key' => ['nullable', 'string', 'max:200', 'regex:/^[A-Za-z0-9_-]*$/'],
            'secret_key' => ['nullable', 'string', 'max:200', 'regex:/^[A-Za-z0-9_-]*$/'],
        ], ['site_key.regex' => 'The site key can only contain letters, numbers, dashes and underscores.', 'secret_key.regex' => 'The secret key can only contain letters, numbers, dashes and underscores.']);

        Setting::put('recaptcha_site_key', trim((string) ($data['site_key'] ?? '')) ?: null);

        if ($request->boolean('secret_remove')) {
            Setting::put('recaptcha_secret_key', null);
        } elseif (filled($data['secret_key'] ?? null)) {
            Setting::put('recaptcha_secret_key', 'enc:'.Crypt::encryptString(trim($data['secret_key'])));
        }
        // A blank secret box keeps the saved one.

        Setting::put('recaptcha_enabled', $request->boolean('enabled') ? '1' : '0');

        return back()->with('status', 'reCAPTCHA settings saved.');
    }

    public function test(Recaptcha $recaptcha)
    {
        $result = $recaptcha->checkSecret(Recaptcha::secret());

        if ($result['ok'] === true) {
            return back()->with('status', $result['message'].' It cannot check the site key: open a form on the website once, tick the box and send a test enquiry.');
        }

        return back()->withErrors(['recaptcha' => $result['message']]);
    }
}
