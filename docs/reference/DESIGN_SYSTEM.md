# Design system

## Direction
Friendly and warm, inspired by the supplied travel-site mock-up (full-bleed hero photo, rounded photo cards, cream sections, one yellow testimonial band, dark footer) but in the Go Belgium palette. The single memorable element is the **quiz card**: a cream card with a thick amber border over the hero photo. Everything else stays calm.

## Tokens (the only place to re-skin: `resources/css/brand.css`)
| Token | Value | Role |
|---|---|---|
| `cream` | `#FCF4DF` | page canvas |
| `cream-deep` | `#F6E8C4` | alternate section tint |
| `paper` | `#FFFDF7` | card surface |
| `sky` / `sky-soft` | `#9DD7E2` / `#D6EEF3` | fresh accent, icon chips, step blocks |
| `slate` | `#58617E` | secondary text, borders |
| `amber` / `amber-deep` | `#FCB345` / `#E89A1C` | actions (buttons, quiz border, testimonial band) |
| `ink` | `#2B2E37` | headings, body text, footer |

Contrast: ink on cream/amber/sky and white on ink/slate pass WCAG AA. Do not set amber text on cream.

Type: **Fredoka** (variable) for headings, **Nunito** (variable) for body, both self-hosted from `public/fonts` with `font-display: swap` and preloaded in the layout. The home hero headline and tagline use **Playfair Display** (`--font-serif`, normal + italic, preloaded on the home page only), with the second line of the headline in italic amber. Headings are sentence case, `text-wrap: balance`, no all-caps labels.

Other tokens: `--radius-card`, `--shadow-card`, `--shadow-lift`. Motion: one hero entrance (`.hero-rise`), modal/quiz transitions only; reduced motion is honoured.

## Components (`resources/views/components`)
- `<x-button variant="primary|dark|sky|outline|light" size="sm|md|lg" href icon>`
- `<x-section-heading title sub align tag tone>`
- `<x-faq-item question>` (native `<details>`)
- `<x-field name label type required hint>` text input with inline error slot
- `<x-enquiry-form form-name mode="standalone|modal|quiz" :package :tour-type :extended id-prefix submit-label>` the one form
- `<x-photo base alt sizes :eager :ratio>` responsive WebP image
- `@icon('name', 'classes')` inline SVG (paths in `App\Support\Icons`)
- Partials: `header` (sticky; `bar` colour #F3ECD8 with plain black links and an amber underline for the current page. On the home page it is transparent with white links until you scroll, driven by an `is-scrolled` class on `<html>` and the `.nav-overlay` rules in `app.css`), `footer`, `quiz`, `enquiry-modal`, `package-card`
- Admin: `<x-admin.input|textarea|rich|select|check>`

## Re-skinning for a new brand
1. Edit the colour and font tokens in `brand.css` (and swap the two `.woff2` files in `public/fonts`, updating the `@font-face` and the preload links in `layouts/app.blade.php`).
2. Replace the wordmark: upload a logo in Admin > Settings (or edit `partials/header.blade.php`).
3. Change copy in Admin > Pages, tour types and quiz; run the seeders on a fresh database for new defaults (`database/seeders`).
4. Swap photos (`public/images/photos` or Admin > Media) and update IMAGE_CREDITS.md.
5. `npm run build`.
