{{-- Package card for /packages, the tour type pages, "You might also like" and the home carousels. Kept as a thin wrapper so existing
     @include('partials.package-card') calls keep working: ALL the markup lives in partials/journey-card.blade.php and ALL the styles
     in the .pkg-card rules of resources/css/app.css. Include with: package, level (heading level, default 3). --}}
@include('partials.journey-card', [
    'package' => $package,
    'level' => $level ?? 3,
])
