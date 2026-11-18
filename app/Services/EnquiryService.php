<?php

namespace App\Services;

use App\Http\Middleware\CaptureTracking;
use App\Jobs\SyncEnquiryToGhl;
use App\Mail\EnquiryReceived;
use App\Models\Enquiry;
use App\Models\Package;
use App\Models\QuizOption;
use App\Models\Setting;
use App\Support\Consent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/** Saves an enquiry with its consent evidence, then queues email and CRM sync. */
class EnquiryService
{
    public function create(array $data, Request $request): Enquiry
    {
        $dial = collect(config('gotour.countries'))->firstWhere(0, $data['country'])[2];
        $national = $dial === '1' ? $data['phone'] : ltrim($data['phone'], '0');

        $package = ! empty($data['package_id']) ? Package::find($data['package_id']) : null;

        $answers = json_decode($data['answers'] ?? '[]', true);
        $answers = is_array($answers) ? $this->cleanAnswers($answers) : [];
        foreach (['season', 'group_size', 'nights'] as $k) {
            if (! empty($data[$k]) && ! isset($answers[$k])) {
                $answers[$k] = $data[$k];
            }
        }

        $enquiry = Enquiry::create([
            'form_name' => $data['form_name'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'country_code' => $data['country'],
            'phone_national' => $data['phone'],
            'phone_e164' => '+'.$dial.$national,
            'travel_date' => $data['travel_date'] ?? null,
            'second_quote' => $data['second_quote'] ?? null,
            'message' => isset($data['message']) ? strip_tags($data['message']) : null,
            'package_id' => $package?->id,
            'package_name' => $package?->title,
            'tour_type' => $data['tour_type'] ?? ($package?->tourType?->name),
            'answers' => $answers ?: null,

            'consent_given' => true,
            'consent_text' => Consent::text(),
            'terms_version' => config('gotour.terms_version'),
            'consent_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'page_url' => $data['page_url'] ?? $request->headers->get('referer'),

            'tracking_first' => CaptureTracking::read($request, 'gt_first') ?: null,
            'tracking_last' => CaptureTracking::read($request, 'gt_last') ?: null,
            'ghl_status' => app(GhlClient::class)->enabled() ? 'pending' : 'skipped',
        ]);

        $this->notifyStaff($enquiry);

        if ($enquiry->ghl_status === 'pending') {
            // Sent straight away (no queue worker needed). A CRM failure must never fail the enquiry: it is recorded
            // on the enquiry and queued for a retry (and can be re-sent with Retry in Admin > Enquiries).
            try {
                SyncEnquiryToGhl::dispatchSync($enquiry->id);
            } catch (\Throwable $e) {
                report($e);
                try {
                    SyncEnquiryToGhl::dispatch($enquiry->id)->delay(now()->addMinutes(2));
                } catch (\Throwable $q) {
                    report($q);
                }
            }
        }

        return $enquiry;
    }

    /** Keep only quiz answers that match a real step key, with short string values. */
    private function cleanAnswers(array $answers): array
    {
        $clean = [];
        foreach ($answers as $key => $value) {
            if (is_string($key) && is_scalar($value) && preg_match('/^[a-z_]{1,40}$/', $key)) {
                $clean[$key] = mb_substr(strip_tags((string) $value), 0, 100);
            }
        }

        return $clean;
    }

    private function notifyStaff(Enquiry $enquiry): void
    {
        $emails = collect(explode(',', (string) Setting::get('notification_emails', '')))
            ->map(fn ($e) => trim($e))->filter(fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL))->values()->all();

        if (! $emails) {
            return;
        }

        try {
            Mail::to($emails)->queue(new EnquiryReceived($enquiry));
        } catch (\Throwable $e) {
            report($e); // never lose the enquiry because email failed
        }
    }

    /** Human labels for stored quiz answers (admin + email). */
    public static function answerLabels(array $answers): array
    {
        $out = [];
        foreach ($answers as $key => $value) {
            $label = QuizOption::whereHas('step', fn ($q) => $q->where('key', $key))->where('value', $value)->value('label');
            $out[$key] = $label ?: $value;
        }

        return $out;
    }
}
