<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\ImageService;
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

    public function update(Request $request, Page $page, ImageService $images)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'seo_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'body' => ['nullable', 'string'],
            'content' => ['nullable', 'array'],
            'content.*' => ['nullable', 'string', 'max:1000'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
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
        if ($request->hasFile('hero_image')) {
            if (blank($request->input('content.hero_image_alt'))) {
                return back()->withErrors(['content.hero_image_alt' => 'Please describe the new hero photo (alt text).'])->withInput();
            }
            $page->content = array_merge($page->content ?? [], ['hero_image' => $images->store($request->file('hero_image'), $request->input('content.hero_image_alt'))->path]);
        }
        $page->save();

        return back()->with('status', 'Page saved.');
    }
}
