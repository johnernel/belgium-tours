<?php

namespace App\Services;

use App\Models\Enquiry;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Everything the admin Dashboard shows, computed from the real data: the enquiries saved by every form (hero quiz, popup, package quote,
 * ask-a-question, contact), their tracking (UTM, click ids, referrer), their CRM status and the sync status of GoHighLevel and the Google Sheet.
 * No figure is invented: an empty database shows zeros and "Nothing yet".
 */
class DashboardStats
{
    public const PERIODS = [7, 30, 90];

    public function __construct(private readonly int $days = 30)
    {
    }

    public static function period(mixed $value): int
    {
        return in_array((int) $value, self::PERIODS, true) ? (int) $value : 30;
    }

    private function since(): Carbon
    {
        return now()->subDays($this->days - 1)->startOfDay();
    }

    /** @return Collection<int, Enquiry> the enquiries in the chosen period */
    public function enquiries(): Collection
    {
        return once(fn () => Enquiry::where('created_at', '>=', $this->since())->latest()->get());
    }

    /** Where the visitor came from (latest touch first): utm source / medium, a Google Ads click, the referring site, or direct. */
    public static function sourceOf(Enquiry $e): string
    {
        foreach ([$e->tracking_last ?? [], $e->tracking_first ?? []] as $t) {
            if (! empty($t['utm_source'])) {
                return strtolower($t['utm_source']).(! empty($t['utm_medium']) ? ' / '.strtolower($t['utm_medium']) : '');
            }
            if (! empty($t['gclid']) || ! empty($t['gbraid']) || ! empty($t['wbraid'])) {
                return 'google / cpc';
            }
            if (! empty($t['referrer']) && ($host = parse_url($t['referrer'], PHP_URL_HOST))) {
                return preg_replace('/^www\./', '', strtolower($host));
            }
        }

        return 'direct';
    }

    /** A click from Google Ads: a click id, or a paid google medium. */
    public static function fromAds(Enquiry $e): bool
    {
        foreach ([$e->tracking_last ?? [], $e->tracking_first ?? []] as $t) {
            if (! empty($t['gclid']) || ! empty($t['gbraid']) || ! empty($t['wbraid'])) {
                return true;
            }
            if (strtolower($t['utm_source'] ?? '') === 'google' && in_array(strtolower($t['utm_medium'] ?? ''), ['cpc', 'ppc', 'paid', 'paidsearch'], true)) {
                return true;
            }
        }

        return false;
    }

    /** @return array<string, int> the biggest first */
    private function tally(Collection $items): array
    {
        return $items->filter()->countBy()->sortDesc()->take(5)->all();
    }

    public function summary(): array
    {
        $list = $this->enquiries();
        $total = $list->count();
        $ads = $list->filter(fn ($e) => self::fromAds($e))->count();
        $won = $list->where('status', 'won')->count();
        $pct = fn (int $n) => $total ? round($n / $total * 100).'%' : '0%';

        return [
            'total' => $total,
            'newInPeriod' => $list->where('status', 'new')->count(),
            'newAll' => Enquiry::where('status', 'new')->count(),
            'ads' => $ads, 'adsPct' => $pct($ads),
            'won' => $won, 'wonPct' => $pct($won),
        ];
    }

    /** One bar per day of the period (zeros included). */
    public function perDay(): array
    {
        $counts = $this->enquiries()->groupBy(fn ($e) => $e->created_at->toDateString())->map->count();
        $days = [];
        for ($d = $this->since()->copy(); $d->lte(now()); $d->addDay()) {
            $days[] = ['date' => $d->copy(), 'n' => $counts[$d->toDateString()] ?? 0];
        }

        return ['days' => $days, 'max' => max(1, collect($days)->max('n'))];
    }

    /** Every status with its count in the period (all statuses, so empty ones still show). */
    public function pipeline(): array
    {
        $by = $this->enquiries()->countBy('status');

        return collect(Enquiry::STATUSES)->map(fn ($label, $key) => ['key' => $key, 'label' => $label, 'n' => (int) ($by[$key] ?? 0)])->values()->all();
    }

    public function sources(): array
    {
        return $this->tally($this->enquiries()->map(fn ($e) => self::sourceOf($e)));
    }

    public function campaigns(): array
    {
        return $this->tally($this->enquiries()->map(fn ($e) => ($e->tracking_last['utm_campaign'] ?? null) ?: ($e->tracking_first['utm_campaign'] ?? null)));
    }

    public function forms(): array
    {
        $labels = ['hero-quiz' => 'Main quiz', 'modal' => 'Quote popup', 'package-quote' => 'Package quote', 'question' => 'Ask a question', 'contact' => 'Contact form'];

        return collect($this->tally($this->enquiries()->pluck('form_name')))->mapWithKeys(fn ($n, $k) => [($labels[$k] ?? $k) => $n])->all();
    }

    public function journeys(): array
    {
        return $this->tally($this->enquiries()->pluck('package_name'));
    }

    public function recent(int $limit = 8): Collection
    {
        return Enquiry::latest()->take($limit)->get();
    }

    /** New enquiries nobody has actioned for more than 24 hours (all time, not just the period). */
    public function waiting(): int
    {
        return Enquiry::where('status', 'new')->where('created_at', '<', now()->subDay())->count();
    }

    public function failedSyncs(): array
    {
        return ['ghl' => Enquiry::where('ghl_status', 'failed')->count(), 'sheet' => Enquiry::where('sheet_status', 'failed')->count()];
    }

    /** The site health checklist: [ok?, title, detail when not ok, admin route for the Fix link]. */
    public function health(): array
    {
        $ghl = app(GhlClient::class);
        $sheet = app(SheetClient::class);
        $placeholders = collect(Setting::all_())->filter(fn ($v) => is_string($v) && str_contains($v, '[CONFIRM'))->count();
        $recaptchaKeyOk = Recaptcha::enabled() && Recaptcha::siteKey() && Recaptcha::secret() && Recaptcha::siteKey() !== '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI';

        return [
            [$recaptchaKeyOk, 'Real reCAPTCHA keys', ! Recaptcha::enabled() ? 'reCAPTCHA is switched off, so spam protection is lighter.' : 'Google\'s test keys (or none) are in use, so spam is not really checked.', 'admin.recaptcha.edit'],
            [$ghl->enabled(), 'GoHighLevel connected', 'Enquiries are not being sent to your CRM.', 'admin.ghl.edit'],
            [$sheet->enabled(), 'Google Sheet log connected', 'Enquiries are not being added to your Google Sheet.', 'admin.sheets.edit'],
            [filled(Setting::get('phone')), 'Phone number set', 'The site shows a placeholder phone number.', 'admin.settings.edit'],
            [filled(Setting::get('gtm_id')), 'Google Tag Manager ID set', 'Conversion events (enquiry_submit, enquiry_thank_you) are not being sent.', 'admin.settings.edit'],
            [filled(Setting::get('notification_emails')), 'Notification email set', 'Nobody is emailed when an enquiry arrives.', 'admin.settings.edit'],
            [$placeholders === 0, 'No [CONFIRM] placeholders in settings', "$placeholders setting(s) still hold a placeholder.", 'admin.settings.edit'],
        ];
    }
}
