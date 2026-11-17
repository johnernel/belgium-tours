<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Page;
use App\Models\QuizStep;
use App\Models\TeamMember;
use App\Models\Testimonial;

/**
 * The pages behind the "About Us" mega menu: Meet Our Team, Testimonials and FAQs. Each one is a photo hero with the
 * main quiz form (like the tour type and Destinations pages) followed by its own content. Sample (placeholder) content
 * keeps a page out of Google until real content replaces it.
 */
class InfoPagesController extends Controller
{
    public function team()
    {
        $members = TeamMember::active()->get();

        return $this->render('team', 'team', [
            'members' => $members,
            'noindex' => $members->isEmpty() || $members->every(fn ($m) => $m->is_placeholder),
        ]);
    }

    public function testimonials()
    {
        $testimonials = Testimonial::active()->get();

        return $this->render('testimonials', 'testimonials', [
            'testimonials' => $testimonials,
            'noindex' => $testimonials->isEmpty() || $testimonials->every(fn ($t) => $t->is_placeholder),
        ]);
    }

    public function faqs()
    {
        return $this->render('faqs', 'faqs', ['faqs' => Faq::active()->get(), 'noindex' => false]);
    }

    private function render(string $slug, string $view, array $data)
    {
        $page = Page::bySlug($slug) ?? abort(404);
        $data['noindex'] = $data['noindex'] || $page->noindex;

        return view('pages.'.$view, $data + [
            'page' => $page,
            'quizSteps' => QuizStep::forQuiz(),
            'quizPreset' => [],
            'quizTourType' => '',
        ]);
    }
}
