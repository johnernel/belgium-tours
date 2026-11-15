# Forms, consent and tracking

## Forms
Hero quiz (final step), header "Start Your Adventure" modal, package "Get a quote", "Ask Us a Question", and the Contact page all render `<x-enquiry-form>` and post to `POST /enquiry`.

Fields: first name*, last name*, email*, country code + phone* (stored E.164), approximate travel date, "second quote from a partner?", special request (question in "ask" mode), plus optional time of year / group size / nights on the modal and contact form. Package forms send the package id and name; tour-type cards send the tour type.

Every form has: reCAPTCHA v2 checkbox (server verified, fails closed), required unticked consent checkbox (browser + server), honeypot, CSRF, per-IP rate limit (6/min), inline errors, friendly success then redirect to `/thank-you`. Without JavaScript the plain form posts normally (reCAPTCHA uses Google's `noscript` fallback).

## Consent
Wording is `config/gotour.php` `consent_template`, with the legal company name from Admin > Settings and the two policy names linking to `/terms-and-conditions` and `/privacy-policy` in a new tab:

> I agree to the Terms and Conditions and Privacy Policy provided by [Company Name]. By providing my phone number, I consent to receive SMS messages from [Company Name] regarding my enquiry, booking updates, itinerary updates, reminders, and customer support. Message and data rates may apply. Reply STOP to unsubscribe or HELP for assistance. Consent is not a condition of purchase.

Evidence stored with every enquiry: consent value, exact text shown, terms version (`terms_version` in the same config), timestamp, IP, user agent, page URL. View it in Admin > Enquiries > an enquiry > Consent evidence. **When you change consent, Terms or Privacy wording, bump `terms_version`.**

## Ad tracking
`CaptureTracking` middleware (public GET pages only) stores two plain-JSON cookies for 30 days:
- `gt_first`: set once, never overwritten.
- `gt_last`: refreshed whenever a visit arrives with ad parameters (or if absent).
Captured: `utm_source`, `utm_medium`, `utm_campaign`, `utm_term`, `utm_content`, `gclid`, `gbraid`, `wbraid`, external `referrer`, `landing_page`. They are saved on the enquiry (`tracking_first` / `tracking_last`), shown in the admin, exported in the CSV and sent to GHL.

## Google Tag Manager and Google Ads conversions
1. Create a GTM web container and paste its ID (`GTM-XXXXXXX`) in Admin > Settings. The snippet is only output when an ID is set.
2. Events pushed to `dataLayer`:
   - `enquiry_submit` with `form_name` (when a form is accepted, just before the redirect)
   - `enquiry_thank_you` (on `/thank-you`, which is `noindex`)
   - `quiz_complete_steps` (visitor reached the quiz contact step)
3. In GTM create a trigger "Custom event = `enquiry_thank_you`". Attach the **Google Ads Conversion Tracking** tag (conversion id/label from Google Ads) to it, plus the Conversion Linker tag on All Pages. Use the Thank You event for the conversion (it fires once per lead, even after redirects).
4. Test with GTM Preview mode. Pass `?gclid=test123` to see the cookie and the saved enquiry carry it.
5. Cookie banner: if you advertise to the EU/UK you need consent management before GTM fires. This starter does not include one (see CONTENT_TO_CONFIRM.md).

## Email notifications
Set "Send new-enquiry emails to" in Admin > Settings (comma separated). Emails are queued (`EnquiryReceived`); locally they land in `storage/logs/laravel.log`.
