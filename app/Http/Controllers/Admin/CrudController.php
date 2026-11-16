<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImageService;
use App\Support\Html;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Small list/create/edit/delete screens driven by a field config.
 * Subclasses set $model, $routeBase and return config() + rules().
 * Field types: text, textarea, rich, number, checkbox, image.
 */
abstract class CrudController extends Controller
{
    /** @var class-string<Model> */
    protected string $model;

    protected string $routeBase;   // e.g. admin.tour-types

    abstract protected function config(): array;

    abstract protected function rules(?Model $record): array;

    protected function orderBy(): string
    {
        return 'sort';
    }

    public function index()
    {
        return view('admin.crud.index', [
            'cfg' => $this->config(), 'routeBase' => $this->routeBase,
            'rows' => $this->model::orderBy($this->orderBy())->get(),
        ]);
    }

    public function create()
    {
        return view('admin.crud.form', ['cfg' => $this->config(), 'routeBase' => $this->routeBase, 'record' => new $this->model(['active' => true, 'sort' => $this->model::max('sort') + 1])]);
    }

    public function store(Request $request, ImageService $images)
    {
        $record = new $this->model;
        $this->save($request, $record, $images);

        return redirect()->route($this->routeBase.'.index')->with('status', $this->config()['singular'].' added.');
    }

    public function edit(Request $request)
    {
        $record = $this->resolveRecord($request);

        return view('admin.crud.form', ['cfg' => $this->config(), 'routeBase' => $this->routeBase, 'record' => $record]);
    }

    public function update(Request $request, ImageService $images)
    {
        $record = $this->resolveRecord($request);
        $this->save($request, $record, $images);

        return redirect()->route($this->routeBase.'.index')->with('status', $this->config()['singular'].' saved.');
    }

    public function destroy(Request $request)
    {
        $this->resolveRecord($request)->delete();

        return redirect()->route($this->routeBase.'.index')->with('status', $this->config()['singular'].' deleted.');
    }

    /** The route has exactly one model parameter; load it by id. */
    protected function resolveRecord(Request $request): Model
    {
        $id = collect($request->route()->parameters())->first();

        return $this->model::findOrFail($id instanceof Model ? $id->getKey() : $id);
    }

    protected function save(Request $request, Model $record, ImageService $images): void
    {
        $data = $request->validate($this->rules($record->exists ? $record : null));

        foreach ($this->config()['fields'] as $f) {
            $name = $f['name'];
            if ($f['type'] === 'image') {
                unset($data[$name]);
                if ($request->hasFile($name)) {
                    $record->{$name} = $images->store($request->file($name), $request->input($name.'_alt'))->path;
                } elseif ($request->boolean($name.'_remove')) {
                    $record->{$name} = null;
                }

                continue;
            }
            if ($f['type'] === 'rich' && array_key_exists($name, $data)) {
                $data[$name] = Html::clean($data[$name]);
            }
            if ($f['type'] === 'checkbox') {
                $data[$name] = $request->boolean($name);
            }
        }

        // A blank (or missing) URL name is built from the name; an existing one is kept.
        if (array_key_exists('slug', $this->rules($record->exists ? $record : null)) && blank($data['slug'] ?? null) && ! empty($data['name'])) {
            $data['slug'] = ($record->exists && $record->slug) ? $record->slug : Str::slug($data['name']);
        }

        $record->fill($data)->save();
    }
}
