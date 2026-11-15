<?php

namespace App\Http\Controllers\Admin;

use App\Models\TourType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class TourTypeController extends CrudController
{
    protected string $model = TourType::class;

    protected string $routeBase = 'admin.tour-types';

    protected function config(): array
    {
        return [
            'title' => 'Tour types', 'singular' => 'Tour type',
            'help' => 'These appear as cards on the home page, in the menu and as package filters.',
            'columns' => ['Name' => 'name', 'Packages' => fn ($r) => $r->packages()->count(), 'Active' => fn ($r) => $r->active ? 'Yes' : 'No'],
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                ['name' => 'slug', 'label' => 'URL name', 'type' => 'text', 'help' => 'Leave blank to create it from the name.'],
                ['name' => 'summary', 'label' => 'Short description', 'type' => 'textarea'],
                ['name' => 'image', 'label' => 'Card photo', 'type' => 'image', 'alt' => 'image_alt'],
                ['name' => 'sort', 'label' => 'Order (smaller shows first)', 'type' => 'number'],
                ['name' => 'active', 'label' => 'Show on the site', 'type' => 'checkbox'],
            ],
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'alpha_dash', 'max:120', Rule::unique('tour_types', 'slug')->ignore($record?->id)],
            'summary' => ['nullable', 'string', 'max:600'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'image_alt' => ['nullable', 'string', 'max:200', 'required_with:image'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
