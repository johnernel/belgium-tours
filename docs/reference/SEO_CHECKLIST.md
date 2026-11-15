# SEO and landing-page checklist (pre-launch)

## Built in
- Every page is server-rendered HTML with one `h1`, logical headings, landmarks and descriptive links.
- Unique title and meta description per page (editable in Admin > Pages and per package), canonical URL, Open Graph and Twitter cards.
- JSON-LD: `TravelAgency` (all pages), `FAQPage` (home and packages with FAQs), `BreadcrumbList` and `TouristTrip` (packages).
- `/sitemap.xml` (home, packages, public pages) and `/robots.txt` (blocks `/admin` and `/thank-you`); the Thank You page is `noindex`. Each page and package has a "hide from search" toggle.
- Images are WebP with width/height, responsive `srcset`, lazy loading below the fold, hero image preloaded and `fetchpriority=high`; fonts are self-hosted with `font-display: swap` and preloaded.
- Little JavaScript (Alpine only); reCAPTCHA loads on first interaction, not on page load.
- Mobile first, tested at 360, 768 and 1280 px with no horizontal scroll; focus styles, WCAG AA contrast, `prefers-reduced-motion` respected.
- Ads best practice: message match in the H1, one clear call to action (the quiz), tap-to-call number, trust badges above the fold, no distracting navigation.

## Before launch
- [ ] Replace every `[CONFIRM: ...]` (CONTENT_TO_CONFIRM.md): phone, email, address, trust badges, reviews, prices, legal text.
- [ ] Set phone, email, address and notification emails in Admin > Settings.
- [ ] Real reCAPTCHA keys for the live domain; GTM ID set; Google Ads conversion attached to the `enquiry_thank_you` event; test a real submission end to end.
- [ ] Set `APP_URL` to the live https address; HTTPS forced; `APP_DEBUG=false`.
- [ ] Send a test enquiry: it appears in Admin, the staff email arrives, GHL shows the contact with UTM fields.
- [ ] Message match: the Google Ads headline and the home page H1/subheading agree (edit in Admin > Pages > Home).
- [ ] Upload a logo, favicon and a default share image.
- [ ] Submit `/sitemap.xml` in Google Search Console; verify the domain.
- [ ] Run Lighthouse (mobile) on `/`, `/packages`, one package and `/contact` against the live site. Targets: Performance, Accessibility, Best Practices, SEO all 90+.
- [ ] Check `/robots.txt` does not block pages you want indexed; confirm `/thank-you` and `/admin` are excluded.
- [ ] Review Terms and Privacy with a professional; add a cookie/consent banner if you target the EU or UK.
- [ ] Confirm image credits (IMAGE_CREDITS.md) are complete and the About page shows them.
- [ ] Back up the database and test restoring it.

## Measured during the build (mobile, simulated slow 4G)
See the final build report. Local numbers come from the PHP dev server on a Windows laptop (slow time to first byte, no gzip), so expect the live server to score the same or better.
