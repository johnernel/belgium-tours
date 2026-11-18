<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use App\Models\Faq;
use App\Models\Package;
use App\Models\Page;
use App\Models\QuizStep;
use App\Models\Testimonial;
use App\Models\TourType;

class HomeController extends Controller
{
    public function index()
    {
        return view('pages.home', [
            'page' => Page::bySlug('home') ?? new Page(['title' => 'Home', 'content' => []]),
            'tourTypes' => TourType::active()->with(['packages' => fn ($q) => $q->published()->orderByDesc('is_featured')->orderBy('sort')])->get(),
            'posts' => \App\Models\Post::published()->limit(3)->get(),
            'experiences' => Experience::active()->withCount(['packages' => fn ($q) => $q->where('is_published', true)])->get(),
            'testimonials' => Testimonial::active()->get(),
            'faqs' => Faq::active()->get(),
            // The main quiz form in the hero (same as the tour type, Destinations and About pages).
            'quizSteps' => QuizStep::forQuiz(),
            'quizPreset' => [],
            'quizTourType' => '',
        ]);
    }
}
