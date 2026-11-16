<?php

namespace App\Http\Controllers\Admin;

use App\Models\Destination;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class DestinationController extends CrudController
{
    protected string $model = Destination::class;

    protected string $routeBase = 'admin.destinations';

    protected function config(): array
    {
        return [
            'title' => 'Regions', 'singular' => 'Region',
            'help' => 'Packages are grouped and filtered by region (for example Belgium, or Belgium and the Netherlands). The themed Top destinations have their own screen.',
            'columns' => ['Name' => 'name', 'Packages' => fn ($r) => $r->packages()->count(), 'Active' => fn ($r) => $r->active ? 'Yes' : 'No'],
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                ['name' => 'slug', 'label' => 'URL name', 'type' => 'text', 'help' => 'Leave blank to create it from the name.'],
                ['name' => 'sort', 'label' => 'Order (smaller shows first)', 'type' => 'number'],
                ['name' => 'active', 'label' => 'Show on the site', 'type' => 'checkbox'],
            ],
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'alpha_dash', 'max:120', Rule::unique('destinations', 'slug')->ignore($record?->id)],
            'sort' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
