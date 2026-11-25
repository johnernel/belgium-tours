<?php

namespace App\Http\Requests;

use App\Services\Recaptcha;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class EnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_name' => trim(strip_tags((string) $this->input('first_name'))),
            'last_name' => trim(strip_tags((string) $this->input('last_name'))),
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone' => self::nationalDigits((string) $this->input('phone'), (string) $this->input('country')),
        ]);
    }

    /**
     * The digits of the number WITHOUT the country dial code and without a leading trunk 0. The form sends the full international number ("+1 2125551234"),
     * the no-JS form and older callers send the national digits only: both end up as the same national digits.
     */
    public static function nationalDigits(string $raw, string $iso): string
    {
        $country = collect(config('gotour.countries'))->firstWhere(0, $iso);
        $dial = $country[2] ?? '';
        $digits = preg_replace('/\D/', '', $raw);
        if ($dial !== '' && str_starts_with(trim($raw), '+') && str_starts_with($digits, $dial)) {
            $digits = substr($digits, strlen($dial));
        }

        return $dial === '1' ? $digits : ltrim($digits, '0');
    }

    public function rules(): array
    {
        $codes = collect(config('gotour.countries'))->pluck(0)->all();

        return [
            'form_name' => ['required', 'in:hero-quiz,modal,package-quote,question,contact'],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'country' => ['required', 'in:'.implode(',', $codes)],
            'phone' => ['required', 'digits_between:5,15'],
            'travel_date' => ['nullable', 'string', 'max:80'],
            'second_quote' => ['nullable', 'in:yes,no,maybe'],
            'message' => ['nullable', 'string', 'max:3000'],
            'package_id' => ['nullable', 'integer', 'exists:packages,id'],
            'tour_type' => ['nullable', 'string', 'max:80'],
            'season' => ['nullable', 'string', 'max:40'],
            'group_size' => ['nullable', 'string', 'max:20'],
            'nights' => ['nullable', 'string', 'max:20'],
            'answers' => ['nullable', 'string', 'max:6000'],
            'page_url' => ['nullable', 'string', 'max:1000'],
            'consent' => ['accepted'],
            'website' => ['max:0'], // honeypot: real people leave it empty
            'g-recaptcha-response' => [Recaptcha::enabled() ? 'required' : 'nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Please enter your first name.',
            'last_name.required' => 'Please enter your last name.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'phone.required' => self::phoneMessage($this->input('country')),
            'phone.digits_between' => self::phoneMessage($this->input('country')),
            'country.required' => 'Please choose your country code.',
            'consent.accepted' => 'Please check the box to agree before sending.',
            'g-recaptcha-response.required' => 'Please check "I\'m not a robot".',
        ];
    }

    /** "Please enter a valid 10-digit phone number." (or a range for countries whose numbers vary in length). */
    public static function phoneMessage(?string $iso): string
    {
        $c = collect(config('gotour.countries'))->firstWhere(0, $iso) ?? ['', '', '', 5, 14];
        [$min, $max] = [$c[3], $c[4]];

        return $min === $max ? "Please enter a valid $min-digit phone number." : "Please enter a valid phone number ($min to $max digits).";
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            // The digit count has to match the selected country (exactly 10 for +1, and so on).
            $c = collect(config('gotour.countries'))->firstWhere(0, $this->input('country'));
            if ($c && ! $v->errors()->has('phone')) {
                $len = strlen((string) $this->input('phone'));
                if ($len < $c[3] || $len > $c[4]) {
                    $v->errors()->add('phone', self::phoneMessage($c[0]));
                }
            }

            if ($v->errors()->isEmpty() && ! app(Recaptcha::class)->verify($this->input('g-recaptcha-response'), $this->ip())) {
                $v->errors()->add('g-recaptcha-response', 'We could not confirm you are human. Please check the box and try again.');
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        // Honeypot trips look like success to bots but are never saved.
        if ($this->filled('website')) {
            throw new HttpResponseException(response()->json(['redirect' => route('thank-you')]));
        }

        parent::failedValidation($validator);
    }
}
