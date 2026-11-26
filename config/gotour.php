<?php

/*
|--------------------------------------------------------------------------
| GoTour brand skeleton settings
|--------------------------------------------------------------------------
| Business rules that are code, not content. Content lives in the database
| (Admin > Settings / Pages). Keep CLAUDE.md in sync with this file.
*/

return [
    // Bump this whenever the Terms, Privacy or consent wording changes.
    // It is stored with every enquiry as chargeback evidence.
    'terms_version' => '2026-12-02-v1',

    // Exact consent wording. {company} is replaced by the legal company name,
    // the two [[...]] markers become links that open in a new tab.
    'consent_template' => 'I agree to the [[Terms and Conditions]] and [[Privacy Policy]] provided by {company}. '
        .'By providing my phone number, I consent to receive SMS messages from {company} regarding my inquiry, '
        .'booking updates, itinerary updates, reminders, and customer support. Message and data rates may apply. '
        .'Reply STOP to unsubscribe or HELP for assistance. Consent is not a condition of purchase.',

    // The name shown in the admin panel (sidebar, login, browser tab). The public site keeps the brand name saved in Admin > Settings.
    'admin_name' => 'Go Belgium Trips',

    // Ad-tracking parameters captured on first touch and kept for 30 days.
    'tracking_keys' => [
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
        'gclid', 'gbraid', 'wbraid',
    ],
    'tracking_days' => 30,

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
        'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
    ],

    'ghl' => [
        'token' => env('GHL_API_TOKEN'),
        'location_id' => env('GHL_LOCATION_ID'),
        'base_url' => 'https://services.leadconnectorhq.com',
        'version' => '2021-07-28',
    ],

    // Google Sheets sync: normally set in Admin > Google Sheets (stored encrypted); these .env values are only a fallback.
    'sheet' => [
        'url' => env('GOOGLE_SHEET_WEBHOOK_URL'),
        'secret' => env('GOOGLE_SHEET_SECRET'),
    ],

    // Country dial codes offered in the phone selector: [ISO, name, dial code, fewest digits, most digits AFTER the dial code (without a leading trunk 0)].
    // The phone field checks the digit count against the selected country in the browser and again on the server.
    'countries' => [
        ['US', 'United States', '1', 10, 10], ['CA', 'Canada', '1', 10, 10], ['GB', 'United Kingdom', '44', 10, 10],
        ['IE', 'Ireland', '353', 8, 9], ['AU', 'Australia', '61', 9, 9], ['NZ', 'New Zealand', '64', 8, 10],
        ['BE', 'Belgium', '32', 8, 9], ['NL', 'Netherlands', '31', 9, 9], ['FR', 'France', '33', 9, 9],
        ['DE', 'Germany', '49', 10, 11], ['ES', 'Spain', '34', 9, 9], ['IT', 'Italy', '39', 9, 10],
        ['CH', 'Switzerland', '41', 9, 9], ['AT', 'Austria', '43', 10, 12], ['SE', 'Sweden', '46', 7, 10],
        ['NO', 'Norway', '47', 8, 8], ['DK', 'Denmark', '45', 8, 8], ['ZA', 'South Africa', '27', 9, 9],
        ['IN', 'India', '91', 10, 10], ['SG', 'Singapore', '65', 8, 8], ['AE', 'United Arab Emirates', '971', 9, 9],
        ['BR', 'Brazil', '55', 10, 11], ['MX', 'Mexico', '52', 10, 10], ['JP', 'Japan', '81', 10, 10],
    ],
];
