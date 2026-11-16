<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use App\Models\Page;
use App\Models\QuizStep;

/** The Destinations page: every "top destination" as a card, under a photo hero with the quiz. */
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
}
