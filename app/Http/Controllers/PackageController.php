<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Package;
use App\Models\QuizStep;
use App\Models\TourType;
use App\Support\PlaceImages;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');
        $destination = $request->query('destination');
        $experience = $request->query('experience');
        $place = mb_substr(trim((string) $request->query('place')), 0, 40);

        $packages = Package::published()->with(['tourType', 'destination'])
            ->when($type, fn ($q) => $q->whereHas('tourType', fn ($t) => $t->where('slug', $type)))
            ->when($destination, fn ($q) => $q->whereHas('destination', fn ($d) => $d->where('slug', $destination)))
            ->when($experience, fn ($q) => $q->whereHas('experiences', fn ($e) => $e->where('slug', $experience)))
            ->when($place !== '', fn ($q) => $q->mentioning($place))
            ->orderByDesc('is_featured')->orderBy('sort')->get();

        return view('pages.packages', [
            'packages' => $packages,
            'tourTypes' => TourType::active()->get(),
            'destinations' => Destination::active()->get(),
            'experiences' => \App\Models\Experience::active()->get(),
            'activeExperience' => $experience,
            'activePlace' => $place,
            'activeType' => $type,
            'activeDestination' => $destination,
        ]);
    }

    public function show(string $slug)
    {
        $package = Package::published()->with(['tourType', 'destination', 'images'])->where('slug', $slug)->firstOrFail();

        $related = Package::published()->where('id', '!=', $package->id)
            ->when($package->tour_type_id, fn ($q) => $q->where('tour_type_id', $package->tour_type_id))
            ->take(3)->get();

        // The main quiz form sits in the hero. The travel-style step is pre-answered from the tour type and the
        // enquiry stays linked to this package.
        $quizSteps = QuizStep::forQuiz();
        $preset = [];
        $value = TourTypeController::TRAVEL_STYLE[$package->tourType?->slug] ?? null;
        $step = collect($quizSteps)->firstWhere('key', 'travel_style');
        if ($value && $step && collect($step['options'])->contains('value', $value)) {
            $preset = ['travel_style' => $value];
        }

        // One image per highlight and per day
        $highlights = array_values((array) $package->highlights);
        $days = array_values((array) $package->itinerary);
        $highlightImages = PlaceImages::forPackage($package, $highlights);
        $dayImages = PlaceImages::forPackage($package, array_map(fn ($d) => ($d['title'] ?? '').' '.($d['text'] ?? ''), $days));

        return view('pages.package', [
            'package' => $package, 'related' => $related,
            'highlights' => $highlights, 'highlightImages' => $highlightImages,
            'days' => $days, 'dayImages' => $dayImages,
            'quizSteps' => $quizSteps, 'quizPreset' => $preset, 'quizTourType' => $package->tourType?->name ?? '', 'quizPackage' => $package,
        ]);
    }
}
