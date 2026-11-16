<?php

namespace App\Http\Controllers\Admin;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Model;

class TestimonialController extends CrudController
{
    protected string $model = Testimonial::class;

    protected string $routeBase = 'admin.testimonials';

    protected function config(): array
    {
        return [
            'title' => 'Testimonials', 'singular' => 'Testimonial',
            'help' => 'Only publish real reviews you have permission to use. Untick "placeholder" once a review is real.',
            'columns' => ['Name' => 'name', 'Quote' => fn ($r) => \Illuminate\Support\Str::limit($r->quote, 70), 'Placeholder' => fn ($r) => $r->is_placeholder ? 'Yes' : 'No', 'Active' => fn ($r) => $r->active ? 'Yes' : 'No'],
            'fields' => [
                ['name' => 'name', 'label' => 'Traveller name', 'type' => 'text', 'required' => true],
                ['name' => 'location', 'label' => 'Home town', 'type' => 'text'],
                ['name' => 'quote', 'label' => 'Review', 'type' => 'textarea', 'required' => true],
                ['name' => 'photo', 'label' => 'Photo (optional)', 'type' => 'image', 'alt' => 'photo_alt', 'help' => 'Only a photo of the traveller who wrote the review, with their permission. Without one, a neutral person icon is shown.'],
                ['name' => 'is_placeholder', 'label' => 'This is placeholder text', 'type' => 'checkbox'],
                ['name' => 'sort', 'label' => 'Order', 'type' => 'number'],
                ['name' => 'active', 'label' => 'Show on the site', 'type' => 'checkbox'],
            ],
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:120'],
            'quote' => ['required', 'string', 'max:1200'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'photo_alt' => ['nullable', 'string', 'max:200', 'required_with:photo'],
            'is_placeholder' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
