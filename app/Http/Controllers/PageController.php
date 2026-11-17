<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\QuizStep;

class PageController extends Controller
{
    public function about()
    {
        // Same hero as the tour type and Destinations pages: photo background with the main quiz form.
        return view('pages.about', [
            'page' => $this->page('about'),
            'quizSteps' => QuizStep::forQuiz(),
            'quizPreset' => [],
            'quizTourType' => '',
        ]);
    }

    public function contact()
    {
        return view('pages.contact', ['page' => $this->page('contact')]);
    }

    public function terms()
    {
        return view('pages.legal', ['page' => $this->page('terms')]);
    }

    public function privacy()
    {
        return view('pages.legal', ['page' => $this->page('privacy')]);
    }

    public function thankYou()
    {
        return view('pages.thank-you');
    }

    private function page(string $slug): Page
    {
        return Page::bySlug($slug) ?? abort(404);
    }
}
