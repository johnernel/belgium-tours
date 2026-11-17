<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\QuizStep;
use App\Models\TourType;

class TourTypeController extends Controller
{
    /** Quiz answer value for the "favourite way to see a country" step, per tour type. */
    public const TRAVEL_STYLE = [
        'self-drive' => 'self-drive',
        'chauffeur-driven' => 'chauffeur',
        'coach-tours' => 'coach',
    ];

    public function show(string $slug)
    {
        $type = TourType::active()->where('slug', $slug)->firstOrFail();
        $quizSteps = QuizStep::forQuiz();

        // The visitor has already told us how they want to travel, so pre-answer that quiz step.
        $preset = [];
        $value = self::TRAVEL_STYLE[$type->slug] ?? null;
        $step = collect($quizSteps)->firstWhere('key', 'travel_style');
        if ($value && $step && collect($step['options'])->contains('value', $value)) {
            $preset = ['travel_style' => $value];
        }

        return view('pages.tour-type', [
            'type' => $type,
            'packages' => Package::published()->where('tour_type_id', $type->id)->with(['tourType', 'destination'])
                ->orderByDesc('is_featured')->orderBy('sort')->get(),
            'others' => TourType::active()->where('id', '!=', $type->id)->get(),
            'quizSteps' => $quizSteps,
            'quizPreset' => $preset,
            'quizTourType' => $type->name,
        ]);
    }
}
