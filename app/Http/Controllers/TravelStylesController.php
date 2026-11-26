<?php

namespace App\Http\Controllers;

use App\Models\QuizStep;
use App\Models\TravelStyle;

/** The Travel Styles page (every style as a card) and one page for each style, both under a photo hero with the quiz. */
class TravelStylesController extends Controller
{
    public function index()
    {
        return view('pages.travel-styles', [
            'styles' => TravelStyle::active()->withCount(['packages' => fn ($q) => $q->where('is_published', true)])->get(),
            'quizSteps' => QuizStep::forQuiz(),
            'quizPreset' => [],
            'quizTourType' => '',
        ]);
    }

    public function show(string $slug)
    {
        $style = TravelStyle::active()->where('slug', $slug)->firstOrFail();

        return view('pages.travel-style', [
            'style' => $style,
            'packages' => $style->packages()->published()->with('tourType')->orderByDesc('is_featured')->orderBy('sort')->get(),
            'others' => TravelStyle::active()->where('id', '!=', $style->id)->withCount(['packages' => fn ($q) => $q->where('is_published', true)])->get(),
            'quizSteps' => QuizStep::forQuiz(),
            'quizPreset' => [],
            'quizTourType' => '',
        ]);
    }
}