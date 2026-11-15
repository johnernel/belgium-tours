<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\Html;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        return view('admin.pages.index', ['pages' => Page::all()->sortBy(fn ($p) => array_search($p->slug, ['home', 'about', 'contact', 'terms', 'privacy']))->values()]);
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', ['page' => $page]);
    }

    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'seo_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'body' => ['nullable', 'string'],
            'content' => ['nullable', 'array'],
            'content.*' => ['nullable', 'string', 'max:1000'],
        ]);

        $page->fill([
            'title' => $data['title'],
            'seo_title' => $data['seo_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'noindex' => $request->boolean('noindex'),
        ]);
        if ($page->slug !== 'home') {
            $page->body = Html::clean($data['body'] ?? null);
        }
        if (isset($data['content'])) {
            $page->content = array_merge($page->content ?? [], $data['content']);
        }
        $page->save();

        return back()->with('status', 'Page saved.');
    }
}
