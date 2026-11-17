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
            'help' => 'Each tour type has its own page (menu: Tour Types), a card on the home page and a package filter.',
            'columns' => ['Name' => 'name', 'Packages' => fn ($r) => $r->packages()->count(), 'Active' => fn ($r) => $r->active ? 'Yes' : 'No'],
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                ['name' => 'tab_label', 'label' => 'Short tab label', 'type' => 'text', 'help' => 'Used on the home page tabs and card tags, e.g. Self-Drive. Blank uses the name.'],
                ['name' => 'slug', 'label' => 'URL name', 'type' => 'text', 'help' => 'Leave blank to create it from the name.'],
                ['name' => 'headline', 'label' => 'Page headline', 'type' => 'text', 'help' => 'The large heading on the tour type page. Blank uses the name.'],
                ['name' => 'summary', 'label' => 'Card summary (home page and other pages)', 'type' => 'textarea'],
                ['name' => 'menu_text', 'label' => 'Menu blurb (one short line)', 'type' => 'text', 'help' => 'Shown under the photo in the Tour Types mega menu. Keep it to about 35 characters so it fits on one line.'],
                ['name' => 'intro', 'label' => 'Page description (beside the quiz)', 'type' => 'textarea', 'help' => 'Two or three short sentences.'],
                ['name' => 'seo_title', 'label' => 'Page title for Google', 'type' => 'text'],
                ['name' => 'meta_description', 'label' => 'Description for Google', 'type' => 'textarea'],
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
            'tab_label' => ['nullable', 'string', 'max:40'],
            'headline' => ['nullable', 'string', 'max:160'],
            'summary' => ['nullable', 'string', 'max:600'],
            'menu_text' => ['nullable', 'string', 'max:60'],
            'intro' => ['nullable', 'string', 'max:700'],
            'seo_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'image_alt' => ['nullable', 'string', 'max:200', 'required_with:image'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
