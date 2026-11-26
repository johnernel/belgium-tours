<?php

namespace App\Http\Controllers\Admin;

use App\Models\TravelStyle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class TravelStyleController extends CrudController
{
    protected string $model = TravelStyle::class;

    protected string $routeBase = 'admin.travel-styles';

    protected function config(): array
    {
        return [
            'title' => 'Travel styles', 'singular' => 'Travel style',
            'help' => 'The themes in the Travel Styles menu (medieval cities, food and chocolate, ...). Each has its own page. Tick the styles a trip belongs to on the package screen; the trip counts are worked out from that.',
            'columns' => ['Name' => 'name', 'Tours' => fn ($r) => $r->packages()->count(), 'Active' => fn ($r) => $r->active ? 'Yes' : 'No'],
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                ['name' => 'slug', 'label' => 'URL name', 'type' => 'text', 'help' => 'Leave blank to create it from the name. The page is /travel-styles/url-name.'],
                ['name' => 'description', 'label' => 'Short line', 'type' => 'textarea', 'help' => 'One sentence. Shown in the menu, on the cards and under the headline.'],
                ['name' => 'intro', 'label' => 'Intro paragraph', 'type' => 'textarea', 'help' => 'Two or three sentences for the style\'s own page. Keep it general: no prices or facts you have not confirmed.'],
                ['name' => 'image', 'label' => 'Photo', 'type' => 'image', 'alt' => 'image_alt'],
                ['name' => 'image_focus', 'label' => 'Photo focus (optional)', 'type' => 'text', 'help' => 'Where to centre the photo when it is cropped, as two percentages, e.g. 30% 50% (left, top).'],
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
            'slug' => ['nullable', 'alpha_dash', 'max:120', Rule::unique('travel_styles', 'slug')->ignore($record?->id)],
            'description' => ['nullable', 'string', 'max:300'],
            'intro' => ['nullable', 'string', 'max:1200'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'image_alt' => ['nullable', 'string', 'max:200', 'required_with:image'],
            'image_focus' => ['nullable', 'regex:/^\d{1,3}% \d{1,3}%$/'],
            'seo_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}