# Go Belgium Tours: GoTour starter site

Server-rendered Laravel 12 landing site for Google Ads (self drive, chauffeur driven and coach tours of Belgium). It captures enquiries reliably, stores consent evidence, tracks ad sources and syncs leads to GoHighLevel (GHL). It is a skeleton the team will iterate on. **Update this file when you change anything it describes.**

## Stack
Laravel 12 / PHP 8.2+ (dev machine runs 8.5), Blade (no SPA), Tailwind v4 + Vite, Alpine.js, MySQL (SQLite in-memory for tests only), custom admin under `/admin`. No Filament, no page builders.

## Run it (XAMPP, Windows)
```
composer install && npm install
copy .env.example .env  &&  php artisan key:generate
php artisan migrate --seed && php artisan storage:link && npm run build
php artisan serve      # http://127.0.0.1:8000   (npm run dev for live CSS/JS)
```
Create the MySQL database `gobelgium` first. Details and troubleshooting: `docs/reference/LOCAL_XAMPP.md`. Admin: `/admin`, login from `ADMIN_SEED_EMAIL` / `ADMIN_SEED_PASSWORD` in `.env`.

## Folder map
- `app/Http/Controllers` public controllers; `Admin/` admin screens (`CrudController` drives the simple list/edit screens).
- `app/Services` `EnquiryService` (save + email + queue GHL), `Recaptcha`, `GhlClient`, `Ghl/GhlFields` (the ONE list of CRM fields), `ImageService` (GD, WebP).
- `app/Support` `Consent` (consent wording), `Icons` (SVG paths, `@icon()` directive), `Html` (rich-text sanitiser).
- `app/Http/Middleware` `CaptureTracking` (first/last touch cookies), `SecurityHeaders`.
- `config/gotour.php` consent template, terms version, tracking keys, countries. `config/credits.php` photo credits.
- `resources/css/brand.css` design tokens (re-skin here); `app.css` public bundle; `admin.css` admin bundle.
- `resources/js/app.js` Alpine: `quiz`, `enquiryForm`, modal store, lazy reCAPTCHA. `admin.js` Trix editor.
- `resources/views` `pages/`, `partials/` (header, footer, quiz, modal), `components/` (button, field, enquiry-form, photo, faq-item...), `admin/`.
- `database/seeders` all starter content (sample data is marked as sample). `docs/reference/` all guides.

## Business rules that are not obvious from the code
- **One form pipeline.** Every form (hero quiz, header modal, package quote, ask-a-question, contact) uses `<x-enquiry-form>` and `POST /enquiry` (`EnquiryController` -> `EnquiryRequest` -> `EnquiryService`). JSON for the Alpine forms, plain redirect for the no-JS fallback.
- **Consent.** Required, never pre-ticked, enforced in browser and server. Wording lives in `config/gotour.php` (`consent_template`) with the company name from settings. Do not edit the words without the client's say-so, and bump `terms_version` whenever consent, Terms or Privacy wording changes. Stored per enquiry: checkbox value, exact text shown, terms version, timestamp, IP, user agent, page URL.
- **reCAPTCHA v2** is verified server-side and fails closed (Google unreachable = rejected). The script loads on demand (first touch of a form, quiz contact step, modal open) to keep first load fast. Local `.env` uses Google's public test keys, which always pass.
- **Spam.** Honeypot field `website` (filled = silent fake success, nothing saved), rate limit 6/min/IP on `/enquiry`, 5/min on admin login.
- **Tracking.** `CaptureTracking` writes plain-JSON cookies `gt_first` (never overwritten) and `gt_last` (refreshed when ad params arrive), 30 days. They are excluded from cookie encryption in `bootstrap/app.php`. Both are stored on the enquiry and sent to GHL.
- **GTM** snippet only renders when `gtm_id` is set (Admin > Settings). Events pushed: `enquiry_submit` (form_name) on submit, `enquiry_thank_you` on `/thank-you`. Attach the Google Ads conversion to the Thank You event in GTM.
- **GHL is OFF when `GHL_API_TOKEN` or `GHL_LOCATION_ID` is empty.** The token lives only in `.env`. Sync runs in a queued job, never blocks or fails the enquiry, retries with backoff and records status in `enquiries.ghl_*`. Field names are matched by exact name via `GhlFields`: rename in GHL and in that class together.
- **Content honesty.** Never invent facts. Unknowns are `[CONFIRM: ...]` placeholders, listed in `docs/reference/CONTENT_TO_CONFIRM.md`. Packages are sample data with no prices (shows "Price on request"). Legal pages are starter templates only.
- **Images.** Only free-licence photos, recorded in `docs/reference/IMAGE_CREDITS.md` and `config/credits.php`. Uploads become WebP at 480/800/1280/1600 px under `storage/app/public/uploads`; seeded photos live in `public/images/photos`. Render with `<x-photo>`.

- **Home hero (current).** Full-bleed photo, large serif headline (last word italic amber), and a glass bar with a "Way to see your destinations" dropdown (tour types, plain GET to /packages?type=slug), a trust-indicator marquee and an arrow button. The 9-step quiz was taken off the home page on purpose: it still exists (`partials/quiz.blade.php`, Alpine `quiz` in app.js, admin quiz editor) and will be placed on inner pages later. Marquee text comes from Admin > Pages > Home (marquee_1 to marquee_4) plus the three trust badges.

## Conventions and gotchas
- Icons: `@icon('name', 'classes')`, add paths in `App\Support\Icons`. (A `<x-icon>` component per icon was measurably slow.)
- Colours/fonts come from tokens in `brand.css`; do not hard-code hex in views.
- Never run tests with a cached config: `php artisan optimize:clear` first. A cached config points tests at the real MySQL database and `RefreshDatabase` will wipe it. (This happened once during the build.)
- `php artisan test` must stay green. Tests blank GHL/reCAPTCHA keys in `phpunit.xml` and fake all HTTP.
- Windows/PowerShell writes UTF-8 with BOM for some redirects; PHP files must be BOM-free.
- `public/robots.txt` must not exist (the route serves it).
- Admin quiz step keys feed GHL fields via `GhlFields::quizFieldMap()`. A brand-new step is still sent inside "Quiz Answers" text; add a mapping to give it its own field.
