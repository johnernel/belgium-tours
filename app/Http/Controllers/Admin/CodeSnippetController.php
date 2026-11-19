<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CodeSnippet;
use App\Models\TourType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Admin > Header & Footer: custom code snippets (tracking pixels, scripts, styles, any HTML) that are output on the public site
 * in the <head>, right after the opening <body> or before the closing </body>, on every page or only on chosen pages.
 * Only signed-in admins can reach it (the whole admin group is behind auth). The code is saved and output exactly as entered.
 */
class CodeSnippetController extends Controller
{
    public function index()
    {
        return view('admin.snippets.index', ['snippets' => CodeSnippet::orderBy('sort')->orderBy('id')->get()]);
    }

    public function create()
    {
        return view('admin.snippets.form', [
            'snippet' => new CodeSnippet(['type' => 'script', 'placement' => 'head', 'scope' => 'all', 'is_active' => true, 'sort' => (int) CodeSnippet::max('sort') + 1, 'pages' => []]),
            'known' => $this->knownPages(),
        ]);
    }

    public function store(Request $request)
    {
        CodeSnippet::create($this->validated($request));

        return redirect()->route('admin.snippets.index')->with('status', 'Code snippet added.');
    }

    public function edit(CodeSnippet $snippet)
    {
        return view('admin.snippets.form', ['snippet' => $snippet, 'known' => $this->knownPages()]);
    }

    public function update(Request $request, CodeSnippet $snippet)
    {
        $snippet->update($this->validated($request));

        return redirect()->route('admin.snippets.index')->with('status', 'Code snippet saved.');
    }

    public function toggle(CodeSnippet $snippet)
    {
        $snippet->update(['is_active' => ! $snippet->is_active]);

        return back()->with('status', '"'.$snippet->name.'" is now '.($snippet->is_active ? 'on.' : 'off.'));
    }

    public function destroy(CodeSnippet $snippet)
    {
        $snippet->delete();

        return redirect()->route('admin.snippets.index')->with('status', 'Code snippet deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in(array_keys(CodeSnippet::TYPES))],
            'placement' => ['required', Rule::in(array_keys(CodeSnippet::PLACEMENTS))],
            'code' => ['required', 'string', 'max:200000'],
            'scope' => ['required', Rule::in(array_keys(CodeSnippet::SCOPES))],
            'pages_pick' => ['nullable', 'array'],
            'pages_pick.*' => ['string', 'max:300'],
            'pages_extra' => ['nullable', 'string', 'max:5000'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ], [
            'code.required' => 'Paste the code to add.',
        ]);

        $pages = collect((array) ($data['pages_pick'] ?? []))
            ->merge(preg_split('/[\r\n,]+/', (string) ($data['pages_extra'] ?? '')))
            ->map(fn ($p) => trim((string) $p))->filter()
            ->map(fn ($p) => CodeSnippet::normalizePath($p))->unique()->values()->all();

        if ($data['scope'] !== 'all' && $pages === []) {
            back()->withInput()->withErrors(['pages_extra' => 'Choose at least one page, or add a page address, or set "Runs on" to the entire website.'])->throwResponse();
        }

        return [
            'name' => $data['name'],
            'type' => $data['type'],
            'placement' => $data['placement'],
            'code' => $data['code'],                       // saved exactly as entered (never sanitised)
            'scope' => $data['scope'],
            'pages' => $data['scope'] === 'all' ? [] : $pages,
            'sort' => $data['sort'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /** Pages offered as tick boxes: path => label. Anything else can be typed in. */
    private function knownPages(): array
    {
        $path = fn (string $name) => CodeSnippet::normalizePath((string) parse_url(route($name), PHP_URL_PATH));

        $pages = [
            '/' => 'Home page',
            $path('packages.index') => 'Packages list',
            $path('destinations.index') => 'Destinations',
            $path('about') => 'About Us',
            $path('team') => 'Meet Our Team',
            $path('testimonials') => 'Testimonials',
            $path('faqs') => 'FAQs',
            $path('journal.index') => 'Journal',
            $path('contact') => 'Contact',
            $path('terms') => 'Terms and Conditions',
            $path('privacy') => 'Privacy Policy',
            $path('thank-you') => 'Thank you page (after a form is sent)',
        ];
        foreach (TourType::active()->get() as $t) {
            $pages['/tours/'.$t->slug] = 'Tour type: '.$t->name;
        }

        return $pages + ['/packages/*' => 'Every single package page', '/tours/*' => 'Every tour type page', '/journal/*' => 'Every Journal article'];
    }
}