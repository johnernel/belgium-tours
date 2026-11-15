<?php

namespace App\Services\Ghl;

/**
 * THE single list of GoHighLevel custom fields this site uses.
 * Fields are matched in GHL by EXACT NAME. To rename one, change it in GHL and here together.
 * Staff may move fields into folders in GHL, but must not rename them.
 */
class GhlFields
{
    public const TEXT = 'TEXT';
    public const LARGE = 'LARGE_TEXT';

    /** @return array<string,string> name => GHL dataType */
    public static function definitions(): array
    {
        $fields = [
            'Preferred Travel Date' => self::TEXT,
            'Second Quote Preference' => self::TEXT,
            'Special Request' => self::LARGE,
            'Package' => self::TEXT,
            'Tour Type' => self::TEXT,
            'Form Name' => self::TEXT,
            'Quiz Answers' => self::LARGE,
            'Consent Given' => self::TEXT,
            'Consent Timestamp' => self::TEXT,
            'Consent Text Version' => self::TEXT,
        ];

        foreach (array_keys(self::quizFieldMap()) as $name) {
            $fields[$name] = self::TEXT;
        }

        foreach (['First', 'Last'] as $touch) {
            foreach (self::trackingLabels() as $label) {
                $fields["$label ($touch)"] = self::TEXT;
            }
        }

        return $fields;
    }

    /** Quiz step key => GHL field name. Add a row here when you add a new quiz step. */
    public static function quizFieldMap(): array
    {
        return [
            'Quiz: Destination' => 'destination',
            'Quiz: Season' => 'season',
            'Quiz: Travel Style' => 'travel_style',
            'Quiz: Group Size' => 'group_size',
            'Quiz: Nights' => 'nights',
            'Quiz: Flights Booked' => 'flights_booked',
            'Quiz: Budget' => 'budget',
            'Quiz: Likelihood' => 'likelihood',
        ];
    }

    /** Tracking key => field label (a "(First)" or "(Last)" suffix is added). */
    public static function trackingLabelMap(): array
    {
        return [
            'utm_source' => 'UTM Source',
            'utm_medium' => 'UTM Medium',
            'utm_campaign' => 'UTM Campaign',
            'utm_term' => 'UTM Term',
            'utm_content' => 'UTM Content',
            'gclid' => 'GCLID',
            'gbraid' => 'GBRAID',
            'wbraid' => 'WBRAID',
            'referrer' => 'Referrer',
            'landing_page' => 'Landing Page',
        ];
    }

    public static function trackingLabels(): array
    {
        return array_values(self::trackingLabelMap());
    }
}
