<?php

namespace App\Support;

use App\Models\Setting;

/**
 * The consent wording shown on every form and stored with every enquiry.
 * The template lives in config/gotour.php; the company name comes from settings.
 */
class Consent
{
    public static function company(): string
    {
        return (string) Setting::get('legal_company_name', Setting::get('brand_name', config('app.name')));
    }

    /** Exact plain text (what we store as evidence). */
    public static function text(): string
    {
        $t = str_replace('{company}', self::company(), config('gotour.consent_template'));

        return str_replace(['[[', ']]'], '', $t);
    }

    /** Same wording with the two policy names as links that open in a new tab. */
    public static function html(): string
    {
        $company = e(self::company());
        $t = e(config('gotour.consent_template'));
        $t = str_replace('{company}', $company, $t);

        $terms = '<a href="'.e(route('terms')).'" target="_blank" rel="noopener" class="underline decoration-amber decoration-2 underline-offset-2 font-semibold">Terms and Conditions</a>';
        $privacy = '<a href="'.e(route('privacy')).'" target="_blank" rel="noopener" class="underline decoration-amber decoration-2 underline-offset-2 font-semibold">Privacy Policy</a>';

        $t = str_replace('[[Terms and Conditions]]', $terms, $t);

        return str_replace('[[Privacy Policy]]', $privacy, $t);
    }
}
