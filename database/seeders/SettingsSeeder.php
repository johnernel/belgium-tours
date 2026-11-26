<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'brand_name' => 'Go Belgium Trips',
            'legal_company_name' => 'Go Belgium Tours',
            'tagline' => 'Belgium tours and tailor-made European vacations',
            // Left blank on purpose until the client confirms them (see CONTENT_TO_CONFIRM.md).
            'phone' => '',
            'email' => '',
            'address' => '',
            'notification_emails' => '',
            'social_facebook' => '',
            'social_instagram' => '',
            'gtm_id' => '',
            'recaptcha_site_key' => '',
            'recaptcha_secret_key' => '',
            'footer_text' => 'Self drive, chauffeur driven and coach tours of Belgium, planned around you.',
            'quote_discount_text' => 'save on your trip',
            'default_meta_description' => 'Belgium tours and tailor-made European vacations: self drive, chauffeur driven and coach tours. Get a free, no-obligation quote.',
            'logo' => '',
            'favicon' => '',
            'default_social_image' => '',
        ];

        foreach ($defaults as $key => $value) {
            if (! Setting::where('key', $key)->exists()) {
                Setting::put($key, $value);
            }
        }
    }
}
