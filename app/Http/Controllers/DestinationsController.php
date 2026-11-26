<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use App\Models\Page;
use App\Models\QuizStep;

/** The Destinations page (every destination as a card) and one page for each destination, both under a photo hero with the quiz. */
class DestinationsController extends Controller
{
    public function index()
    {
        return view('pages.destinations', [
            'page' => Page::bySlug('destinations') ?? abort(404),
            'experiences' => Experience::active()->withCount(['packages' => fn ($q) => $q->where('is_published', true)])->get(),
            'quizSteps' => QuizStep::forQuiz(),
            'quizPreset' => [],
            'quizTourType' => '',
        ]);
    }

    public function show(string $slug)
    {
        $destination = Experience::active()->where('slug', $slug)->firstOrFail();

        return view('pages.destination', [
            'destination' => $destination,
            'packages' => $destination->packages()->published()->with('tourType')->orderByDesc('is_featured')->orderBy('sort')->get(),
            'others' => Experience::active()->where('id', '!=', $destination->id)->withCount(['packages' => fn ($q) => $q->where('is_published', true)])->get(),
            'quizSteps' => QuizStep::forQuiz(),
            'quizPreset' => [],
            'quizTourType' => '',
        ]);
    }
}