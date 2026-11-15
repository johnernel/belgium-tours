<?php

namespace App\Http\Controllers;

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
            'tourTypes' => TourType::active()->get(),
            'packages' => Package::published()->with(['tourType', 'destination'])->orderByDesc('is_featured')->orderBy('sort')->take(6)->get(),
            'testimonials' => Testimonial::active()->get(),
            'faqs' => Faq::active()->get(),
            'quizSteps' => QuizStep::forQuiz(),
        ]);
    }
}
