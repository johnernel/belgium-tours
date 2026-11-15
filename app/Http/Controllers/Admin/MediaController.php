<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\ImageService;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index()
    {
        return view('admin.media', ['items' => Media::latest()->paginate(24)]);
    }

    public function store(Request $request, ImageService $images)
    {
        $request->validate([
            'files' => ['required', 'array', 'max:20'],
            'files.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:'.ImageService::MAX_KB],
            'alt' => ['required', 'string', 'max:200'],
        ], ['alt.required' => 'Please describe the photo (alt text). It helps accessibility and search.']);

        foreach ($request->file('files') as $file) {
            $images->store($file, $request->input('alt'));
        }

        return back()->with('status', count($request->file('files')).' photo(s) uploaded and optimised.');
    }

    public function destroy(Media $media, ImageService $images)
    {
        $images->delete($media);

        return back()->with('status', 'Photo deleted. Anything still using it will show a gap, so check your packages.');
    }
}
