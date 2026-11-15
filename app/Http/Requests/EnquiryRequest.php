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
            'phone' => preg_replace('/[^\d]/', '', (string) $this->input('phone')),
        ]);
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
            'phone' => ['required', 'digits_between:5,14'],
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
            'g-recaptcha-response' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Please enter your first name.',
            'last_name.required' => 'Please enter your last name.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'phone.required' => 'Please enter your phone number.',
            'phone.digits_between' => 'Please enter a valid phone number (numbers only, without the country code).',
            'country.required' => 'Please choose your country code.',
            'consent.accepted' => 'Please tick the box to agree before sending.',
            'g-recaptcha-response.required' => 'Please tick "I\'m not a robot".',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($v->errors()->isEmpty() && ! app(Recaptcha::class)->verify($this->input('g-recaptcha-response'), $this->ip())) {
                $v->errors()->add('g-recaptcha-response', 'We could not confirm you are human. Please tick the box and try again.');
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
