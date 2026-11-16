<?php

namespace App\Http\Controllers\Admin;

use App\Models\Experience;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class ExperienceController extends CrudController
{
    protected string $model = Experience::class;

    protected string $routeBase = 'admin.experiences';

    protected function config(): array
    {
        return [
            'title' => 'Top destinations', 'singular' => 'Destination',
            'help' => 'The themed destinations shown as tabs on the home page and as cards on the Destinations page. Assign tours to them on each package.',
            'columns' => ['Name' => 'name', 'Tours' => fn ($r) => $r->packages()->count(), 'Active' => fn ($r) => $r->active ? 'Yes' : 'No'],
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                ['name' => 'slug', 'label' => 'URL name', 'type' => 'text', 'help' => 'Leave blank to create it from the name.'],
                ['name' => 'description', 'label' => 'Short description', 'type' => 'textarea', 'help' => 'Two sentences. Shown next to the photo and on the card.'],
                ['name' => 'image', 'label' => 'Photo', 'type' => 'image', 'alt' => 'image_alt'],
                ['name' => 'sort', 'label' => 'Order (smaller shows first)', 'type' => 'number'],
                ['name' => 'active', 'label' => 'Show on the site', 'type' => 'checkbox'],
            ],
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'alpha_dash', 'max:120', Rule::unique('experiences', 'slug')->ignore($record?->id)],
            'description' => ['nullable', 'string', 'max:600'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'image_alt' => ['nullable', 'string', 'max:200', 'required_with:image'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
