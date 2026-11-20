# SEO and landing-page checklist (pre-launch)

## Built in
- Every page is server-rendered HTML with one `h1`, logical headings, landmarks and descriptive links.
- Unique title and meta description per page (editable in Admin > Pages and per package), canonical URL, Open Graph and Twitter cards.
- JSON-LD: `TravelAgency` (all pages), `FAQPage` (the FAQs page and packages with FAQs; the redesigned home page has no FAQ section, so it has no FAQ schema), `BreadcrumbList` and `TouristTrip` (packages).
- `/sitemap.xml` (home, packages, public pages) and `/robots.txt` (blocks `/admin` and `/thank-you`); the Thank You page is `noindex`. Each page and package has a "hide from search" toggle.
- Images are WebP with width/height, responsive `srcset`, lazy loading below the fold, hero image preloaded and `fetchpriority=high`; fonts are self-hosted with `font-display: swap` and preloaded.
- Little JavaScript (Alpine only); reCAPTCHA loads on first interaction, not on page load.
- Mobile first, tested at 320, 360, 390, 768 and 1440 px with no horizontal scroll; focus styles, WCAG AA contrast (Lighthouse accessibility 100 on the tested pages), 24px minimum touch targets on the carousel dots, `prefers-reduced-motion` respected.
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
- [ ] Run Lighthouse (mobile) on `/packages` and `/contact` against the live site too (the home page and one package page are done, see below). Target: Performance, Accessibility, Best Practices and SEO all 95+.
- [ ] Check `/robots.txt` does not block pages you want indexed; confirm `/thank-you` and `/admin` are excluded.
- [ ] Review Terms and Privacy with a professional; add a cookie/consent banner if you target the EU or UK.
- [ ] Confirm image credits (IMAGE_CREDITS.md) are complete and the About page shows them.
- [ ] Back up the database and test restoring it.

## Lighthouse audit (2026-11-20)
Tool: Lighthouse 13 (Chrome headless). Mobile = Lighthouse's default simulated Moto G on slow 4G; desktop = the desktop preset. Pages: the home page `/` and one package page (`/packages/bruges-ghent-medieval-escape`). Each cell is Performance / Accessibility / Best Practices / SEO.

**Live site, before the fixes** (https://slategrey-chimpanzee-513787.hostingersite.com)

| Page | Desktop | Mobile |
|---|---|---|
| Home | 98 / **92** / 100 / 100 | 96 / **89** / 100 / 100 |
| Package | 99 / **86** / 100 / 100 | 96 / **86** / 100 / 100 |

Only Accessibility was under 95. Performance, Best Practices and SEO were already 95+ (LCP 0.6 to 0.8 s desktop, 2.6 s mobile, CLS 0 to 0.03, TBT under 100 ms).

**What was failing and how it was fixed**
1. **Colour contrast, white text on the amber buttons** (1.8:1 against the 4.5:1 needed). The `cta` button variant (header "Enquire Now", "Get a Quote", "Plan My Journey", ...) keeps its white text but now sits on the deep amber `amber-ink` (#9A5B00, 5.4:1) instead of #FCB345. Hover is unchanged (light blue with dark text). To get the old bright amber back the text has to go dark again (`primary` variant).
2. **Colour contrast, small amber text on the slate package card (phones)** (3.4:1): the "N nights · route" label now uses the lighter `amber-light` token. The same token was lightened to #FFE3AD (about 5.3:1) so the quiz title accent "Seconds" passes too (it was 4.38:1).
3. **Links without a name:** the logo link in both headers had only an `aria-hidden` white logo while the header was transparent. Both now have `aria-label="Go Belgium Tours home"`.
4. **Touch targets under 24 px:** the carousel pagination dots (home carousels) are now 24 px tall buttons around the same small visible dots, spaced 24 px apart.
5. **Invalid definition list (package page):** the four overview facts (duration, tour type, route, destination) were a `<dl>` with wrapper divs; they are now a plain `<ul>`.

**After the fixes** (local, `php artisan serve`; Accessibility, Best Practices and SEO do not depend on the server)

| Page | Desktop | Mobile |
|---|---|---|
| Home | 91* / 100 / 100 / 100 | 65* / 100 / 100 / 100 |
| Package | 97* / 100 / 100 / 100 | 78* / 100 / 100 / 100 |

*Performance from the PHP dev server only (about 1.4 s to first byte, no compression or caching), not representative: the live server scored 96 to 99 on the same pages before these changes, and the fixes add no weight (markup and colour changes only). **Re-run `lighthouse https://<live-url>/ --preset=desktop` and the mobile default after the next deploy and record the live numbers here.**

**Not done on purpose (already 95+, small gains)**
- The root document takes 0.7 to 0.9 s on Hostinger (server response), and the main stylesheet (23 KB) blocks rendering for about 150 ms. Both are fine at today's scores; a CDN or a page cache would be the next step if Performance ever drops below 95.
- The hero and card photos are slightly larger than their display size on phones (Lighthouse estimates 413 KiB of savings across the page); regenerating a 640 px size would trim it.

## Measured during the build (mobile, simulated slow 4G)
See the final build report. Local numbers come from the PHP dev server on a Windows laptop (slow time to first byte, no gzip), so expect the live server to score the same or better.