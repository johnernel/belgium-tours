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
    'terms_version' => '2026-11-15-v1',

    // Exact consent wording. {company} is replaced by the legal company name,
    // the two [[...]] markers become links that open in a new tab.
    'consent_template' => 'I agree to the [[Terms and Conditions]] and [[Privacy Policy]] provided by {company}. '
        .'By providing my phone number, I consent to receive SMS messages from {company} regarding my enquiry, '
        .'booking updates, itinerary updates, reminders, and customer support. Message and data rates may apply. '
        .'Reply STOP to unsubscribe or HELP for assistance. Consent is not a condition of purchase.',

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

    // Country dial codes offered in the phone selector: [ISO, name, dial code]
    'countries' => [
        ['US', 'United States', '1'], ['CA', 'Canada', '1'], ['GB', 'United Kingdom', '44'],
        ['IE', 'Ireland', '353'], ['AU', 'Australia', '61'], ['NZ', 'New Zealand', '64'],
        ['BE', 'Belgium', '32'], ['NL', 'Netherlands', '31'], ['FR', 'France', '33'],
        ['DE', 'Germany', '49'], ['ES', 'Spain', '34'], ['IT', 'Italy', '39'],
        ['CH', 'Switzerland', '41'], ['AT', 'Austria', '43'], ['SE', 'Sweden', '46'],
        ['NO', 'Norway', '47'], ['DK', 'Denmark', '45'], ['ZA', 'South Africa', '27'],
        ['IN', 'India', '91'], ['SG', 'Singapore', '65'], ['AE', 'United Arab Emirates', '971'],
        ['BR', 'Brazil', '55'], ['MX', 'Mexico', '52'], ['JP', 'Japan', '81'],
    ],
];
