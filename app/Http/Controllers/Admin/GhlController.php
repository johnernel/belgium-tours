<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\GhlClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/**
 * Admin > GoHighLevel: the Location ID and the Private Integration token (stored encrypted), an on/off switch, a
 * connection test and the one-click creation of the custom fields. Every form on the site shares one enquiry pipeline,
 * so saving valid credentials here connects all of them.
 */
class GhlController extends Controller
{
    public function edit(GhlClient $ghl)
    {
        $token = $ghl->token();

        return view('admin.ghl', [
            'status' => $ghl->enabled() ? 'on' : ($ghl->paused() ? 'paused' : 'off'),
            'source' => $ghl->source(),
            'locationId' => $ghl->locationId(),
            'tokenHint' => $token ? '••••••••'.substr($token, -4) : null,
            'tokenSaved' => filled(Setting::get('ghl_api_token')),
            'sendOn' => ! $ghl->paused(),
            'fieldCount' => count(\App\Services\Ghl\GhlFields::definitions()),
        ]);
    }

    public function update(Request $request, GhlClient $ghl)
    {
        $data = $request->validate([
            'location_id' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]*$/'],
            'api_token' => ['nullable', 'string', 'max:400'],
        ], ['location_id.regex' => 'The Location ID can only contain letters, numbers, dashes and underscores.']);

        Setting::put('ghl_location_id', trim((string) ($data['location_id'] ?? '')) ?: null);

        if ($request->boolean('token_remove')) {
            Setting::put('ghl_api_token', null);
        } elseif (filled($data['api_token'] ?? null)) {
            Setting::put('ghl_api_token', Crypt::encryptString(trim($data['api_token'])));
        }
        // A blank token box keeps the saved one.

        Setting::put('ghl_enabled', $request->boolean('send') ? '1' : '0');
        \Illuminate\Support\Facades\Cache::forget('ghl.field_ids.'.$ghl->locationId());

        return back()->with('status', 'GoHighLevel settings saved.');
    }

    public function test(GhlClient $ghl)
    {
        if (blank($ghl->token()) || blank($ghl->locationId())) {
            return back()->withErrors(['ghl' => 'Add and save both the Location ID and the API token first.']);
        }

        try {
            $check = $ghl->checkConnection();
            if (! $check['ok']) {
                $why = match ($check['status']) {
                    401, 403 => 'GoHighLevel rejected the token or its permissions (error '.$check['status'].'). Check the token is a Private Integration token with access to this location.',
                    404 => 'GoHighLevel does not know that Location ID (error 404). Check it in GoHighLevel > Settings > Business Profile.',
                    default => 'GoHighLevel answered with error '.$check['status'].'.',
                };

                return back()->withErrors(['ghl' => $why]);
            }
            $fields = $ghl->fieldStatus();
        } catch (\Throwable $e) {
            return back()->withErrors(['ghl' => 'Could not reach GoHighLevel: '.mb_substr($e->getMessage(), 0, 160)]);
        }

        $have = $fields['total'] - count($fields['missing']);
        $msg = 'Connected to '.($check['name'] ?: 'your location').". $have of {$fields['total']} custom fields exist.";
        if ($fields['missing']) {
            $msg .= ' '.count($fields['missing']).' are missing: press "Create missing custom fields".';
        }

        return back()->with('status', $msg);
    }

    public function setup(GhlClient $ghl)
    {
        if (blank($ghl->token()) || blank($ghl->locationId())) {
            return back()->withErrors(['ghl' => 'Add and save both the Location ID and the API token first.']);
        }

        try {
            $r = $ghl->setupFields();
        } catch (\Throwable $e) {
            return back()->withErrors(['ghl' => 'Could not list the custom fields: '.mb_substr($e->getMessage(), 0, 160).' Check the token permissions (see docs/reference/GHL_INTEGRATION.md).']);
        }

        if ($r['failed']) {
            return back()->withErrors(['ghl' => "Created {$r['created']}, already existed {$r['existing']}, but {$r['failed'][0]}".(count($r['failed']) > 1 ? ' (and '.(count($r['failed']) - 1).' more)' : '')]);
        }

        return back()->with('status', "Custom fields ready: created {$r['created']}, already existed {$r['existing']} (of {$r['total']}).");
    }
}
