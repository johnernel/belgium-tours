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
 * Field types: text, textarea, rich, number, checkbox, image, relation (a tick-box list that syncs a many-to-many: needs 'relation' = the method name and 'options' = a closure returning id => label).
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

    /** 'asc' or 'desc' for orderBy(). */
    protected function orderDirection(): string
    {
        return 'asc';
    }

    /** A blank record for the Add screen. Screens whose table has no sort column override this. */
    protected function newRecord(): Model
    {
        return new $this->model(['active' => true, 'sort' => $this->model::max('sort') + 1]);
    }

    public function index()
    {
        return view('admin.crud.index', [
            'cfg' => $this->config(), 'routeBase' => $this->routeBase,
            'rows' => $this->model::orderBy($this->orderBy(), $this->orderDirection())->get(),
        ]);
    }

    public function create()
    {
        return view('admin.crud.form', ['cfg' => $this->config(), 'routeBase' => $this->routeBase, 'record' => $this->newRecord()]);
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
            if ($f['type'] === 'relation') {
                unset($data[$name]); // a link list, not a column: synced after the record is saved
                continue;
            }
            if ($f['type'] === 'rich' && array_key_exists($name, $data)) {
                $data[$name] = Html::clean($data[$name]);
            }
            if ($f['type'] === 'checkbox') {
                $data[$name] = $request->boolean($name);
            }
        }

        // A blank (or missing) URL name is built from the name (or title); an existing one is kept.
        if (array_key_exists('slug', $this->rules($record->exists ? $record : null)) && blank($data['slug'] ?? null) && ! empty($data['name'] ?? $data['title'] ?? null)) {
            $data['slug'] = ($record->exists && $record->slug) ? $record->slug : Str::slug($data['name'] ?? $data['title']);
        }

        $record->fill($data)->save();

        // Many-to-many link lists ("Tours"): replaced by exactly what is ticked, but only when the list was on the submitted form.
        foreach ($this->config()['fields'] as $f) {
            if ($f['type'] === 'relation' && $request->boolean($f['name'].'_present')) {
                $record->{$f['relation']}()->sync(array_map('intval', (array) $request->input($f['name'], [])));
            }
        }
    }
}
