# Homepage swap: how to revert

On the client's request the home page designs were swapped (local first, then live only when the client says so):

- `/` (`pages/home.blade.php`) now shows the redesign that used to be the "Homepage Test" (contact strip + mega-menu header, new hero and quiz card, feature strip, tailor-made, tour type cards, package carousels, why choose us, stress-free band, testimonials with video, destinations, payments band, plan band, new footer).
- `/homepage-test` (`pages/home-test.blade.php`) now shows the previous home design (noindex, not in the sitemap).
- Controller, routes, forms, quiz, quote popup, GoHighLevel and Google Sheets integrations are unchanged: both pages use the same data and the same enquiry pipeline.

## Backups taken before the swap
- Git tag `pre-homepage-swap` (the commit right before the swap).
- File copies in `docs/backups/homepage-swap/`: `home.blade.php.bak`, `home-test.blade.php.bak`, `HomeController.php.bak`, `web.php.bak`, `CLAUDE.md.bak`, `HomepageTestPageTest.php.bak`.

## Revert (local)
```
git checkout pre-homepage-swap -- resources/views/pages/home.blade.php resources/views/pages/home-test.blade.php tests/Feature CLAUDE.md
php artisan optimize:clear && php artisan test
```
or copy the `.bak` files back over the originals (drop the `.bak`). The old files keep the same names, so nothing else needs changing. If the swap has already been deployed, redeploy after reverting (docs/reference/DEPLOYMENT.md section 8).

## Alternative: swap back without git
Swap the two view names inside `HomeController` (`index` and `test`), then fix the page titles and `noindex` sections at the top of both blade files (the home page must NOT have `@section('noindex')` unless the page record says so).