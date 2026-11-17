{{-- Package card for /packages, the tour type pages and "You might also like": the same layout as the Signature Journeys
     card (journey-card) on a solid #58617E background. Include with: package, level (heading level, default 3). --}}
@include('partials.journey-card', [
    'package' => $package,
    'tag' => $package->tourType?->tab_label ?: ($package->tourType?->name ?? 'Tour'),
    'solid' => true,
    'level' => $level ?? 3,
])
