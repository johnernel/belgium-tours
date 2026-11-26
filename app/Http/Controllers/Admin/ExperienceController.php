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
            'title' => 'Destinations', 'singular' => 'Destination',
            'help' => 'The places shown in the home page accordion and as cards on the Destinations page (Bruges, Ghent, ...). Tour counts are worked out from the tours you assign on each package.',
            'columns' => ['Name' => 'name', 'Tours' => fn ($r) => $r->packages()->count(), 'Active' => fn ($r) => $r->active ? 'Yes' : 'No'],
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                ['name' => 'slug', 'label' => 'URL name', 'type' => 'text', 'help' => 'Leave blank to create it from the name.'],
                ['name' => 'description', 'label' => 'Short description', 'type' => 'textarea', 'help' => 'One or two sentences. Shown in the menu, on the cards and under the headline of the destination page.'],
                ['name' => 'intro', 'label' => 'Intro paragraph', 'type' => 'textarea', 'help' => 'Two or three sentences for the destination\'s own page (/destinations/url-name). Keep it general: no prices or facts you have not confirmed.'],
                ['name' => 'image', 'label' => 'Photo', 'type' => 'image', 'alt' => 'image_alt'],
                ['name' => 'image_focus', 'label' => 'Photo focus (optional)', 'type' => 'text', 'help' => 'Where to centre the photo in the tall strips, as two percentages, e.g. 30% 50% (left, top).'],
                ['name' => 'seo_title', 'label' => 'Search title (optional)', 'type' => 'text'],
                ['name' => 'meta_description', 'label' => 'Search description (optional)', 'type' => 'textarea'],
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
            'intro' => ['nullable', 'string', 'max:1200'],
            'seo_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'image_alt' => ['nullable', 'string', 'max:200', 'required_with:image'],
            'image_focus' => ['nullable', 'regex:/^\d{1,3}% \d{1,3}%$/'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
