<?php

namespace App\Http\Controllers;

use App\Models\Post;

/** The Journal: a list of articles and one page per article. */
class JournalController extends Controller
{
    public function index()
    {
        $posts = Post::published()->get();

        return view('pages.journal-index', [
            'posts' => $posts,
            // Sample articles are not for Google: only index the list once at least one real article exists.
            'indexable' => $posts->contains(fn ($p) => ! $p->is_placeholder),
        ]);
    }

    public function show(string $slug)
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();

        return view('pages.journal-show', [
            'post' => $post,
            'more' => Post::published()->where('id', '!=', $post->id)->limit(3)->get(),
        ]);
    }
}
