<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizOption;
use App\Models\QuizStep;
use App\Support\Icons;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    public function index()
    {
        return view('admin.quiz.index', [
            'steps' => QuizStep::with('options')->orderBy('sort')->get(),
            'icons' => array_combine(array_keys(Icons::all()), array_keys(Icons::all())),
        ]);
    }

    public function storeStep(Request $request)
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:tiles,select'],
        ]);

        $key = Str::snake(Str::limit(Str::slug($data['question'], '_'), 30, ''));
        $base = $key ?: 'step';
        $i = 2;
        while (QuizStep::where('key', $key)->exists()) {
            $key = $base.'_'.$i++;
        }

        QuizStep::create($data + ['key' => $key, 'sort' => QuizStep::max('sort') + 1, 'active' => false]);

        return back()->with('status', 'Step added but switched off. Add its options, then switch it on.');
    }

    public function updateStep(Request $request, QuizStep $step)
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:tiles,select'],
            'sort' => ['nullable', 'integer', 'min:0'],
        ]);
        $step->update($data + ['active' => $request->boolean('active')]);

        return back()->with('status', 'Step saved.');
    }

    public function destroyStep(QuizStep $step)
    {
        $step->delete();

        return back()->with('status', 'Step deleted.');
    }

    public function storeOption(Request $request, QuizStep $step)
    {
        $data = $request->validate(['label' => ['required', 'string', 'max:120'], 'icon' => ['nullable', 'string', 'max:40']]);
        $step->options()->create([
            'label' => $data['label'], 'value' => Str::slug($data['label']) ?: Str::random(6),
            'icon' => $data['icon'] ?? null, 'sort' => ($step->options()->max('sort') ?? 0) + 1,
        ]);

        return back()->with('status', 'Option added.');
    }

    public function updateOption(Request $request, QuizOption $option)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:120'], 'value' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:40'], 'sort' => ['nullable', 'integer', 'min:0'],
        ]);
        $option->update($data + ['active' => $request->boolean('active')]);

        return back()->with('status', 'Option saved.');
    }

    public function destroyOption(QuizOption $option)
    {
        $option->delete();

        return back()->with('status', 'Option deleted.');
    }
}
