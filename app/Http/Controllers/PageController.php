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
            // The home sections shown on About Us use the home page copy (Admin > Pages > Home) and the same data.
            'home' => Page::bySlug('home') ?? new Page(['title' => 'Home', 'content' => []]),
            'tourTypes' => \App\Models\TourType::active()->with(['packages' => fn ($q) => $q->published()->orderByDesc('is_featured')->orderBy('sort')])->get(),
            'testimonials' => \App\Models\Testimonial::active()->get(),
            'posts' => \App\Models\Post::published()->limit(3)->get(),
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
