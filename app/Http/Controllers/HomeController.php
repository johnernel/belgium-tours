<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Package;
use App\Models\Page;
use App\Models\Testimonial;
use App\Models\TourType;

class HomeController extends Controller
{
    public function index()
    {
        return view('pages.home', [
            'page' => Page::bySlug('home') ?? new Page(['title' => 'Home', 'content' => []]),
            'tourTypes' => TourType::active()->with(['packages' => fn ($q) => $q->published()->orderByDesc('is_featured')->orderBy('sort')])->get(),
            'testimonials' => Testimonial::active()->get(),
            'faqs' => Faq::active()->get(),
        ]);
    }
}
