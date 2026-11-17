<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\GhlClient;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /** Plain text settings saved as-is. */
    private const TEXT = [
        'brand_name', 'legal_company_name', 'tagline', 'phone', 'email', 'address', 'notification_emails',
        'social_facebook', 'social_instagram', 'gtm_id',
        'footer_text', 'quote_discount_text', 'default_meta_description',
    ];

    public function edit(GhlClient $ghl)
    {
        return view('admin.settings', [
            's' => Setting::all_(),
            'ghlOn' => $ghl->enabled(),
            'tokenSet' => filled(config('gotour.ghl.token')),
            'locationSet' => filled(config('gotour.ghl.location_id')),
        ]);
    }

    public function update(Request $request, ImageService $images)
    {
        $data = $request->validate([
            'brand_name' => ['required', 'string', 'max:120'],
            'legal_company_name' => ['required', 'string', 'max:160'],
            'tagline' => ['nullable', 'string', 'max:200'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:190'],
            'address' => ['nullable', 'string', 'max:300'],
            'notification_emails' => ['nullable', 'string', 'max:500', function ($attr, $value, $fail) {
                foreach (array_filter(array_map('trim', explode(',', (string) $value))) as $e) {
                    if (! filter_var($e, FILTER_VALIDATE_EMAIL)) {
                        $fail("\"$e\" is not a valid email address.");
                    }
                }
            }],
            'social_facebook' => ['nullable', 'url', 'max:255'],
            'social_instagram' => ['nullable', 'url', 'max:255'],
            'gtm_id' => ['nullable', 'regex:/^GTM-[A-Z0-9]{4,10}$/'],
            'footer_text' => ['nullable', 'string', 'max:300'],
            'quote_discount_text' => ['nullable', 'string', 'max:100'],
            'default_meta_description' => ['nullable', 'string', 'max:320'],
            'logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:png,ico', 'max:512'],
            'default_social_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ], ['gtm_id.regex' => 'The Tag Manager ID looks like GTM-XXXXXXX.']);

        foreach (self::TEXT as $key) {
            Setting::put($key, $data[$key] ?? null);
        }

        foreach (['logo' => 'branding', 'favicon' => 'branding'] as $field => $dir) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $path = $file->storeAs($dir, $field.'-'.time().'.'.$file->extension(), 'public');
                Setting::put($field, 'storage/'.$path);
            } elseif ($request->boolean($field.'_remove')) {
                Setting::put($field, null);
            }
        }
        if ($request->hasFile('default_social_image')) {
            Setting::put('default_social_image', $images->store($request->file('default_social_image'))->path.'-1600.webp');
        }

        return back()->with('status', 'Settings saved.');
    }
}
