<?php

namespace App\Http\Controllers\Admin;

use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Model;

class TeamMemberController extends CrudController
{
    protected string $model = TeamMember::class;

    protected string $routeBase = 'admin.team';

    protected function config(): array
    {
        return [
            'title' => 'Team', 'singular' => 'Team member',
            'help' => 'The people shown on the Meet Our Team page. Only add real people, with their permission, and only photos you are free to use. Sample entries are placeholders: replace them and untick "placeholder" (the page is hidden from Google while placeholders remain).',
            'columns' => ['Name' => 'name', 'Role' => 'role', 'Placeholder' => fn ($r) => $r->is_placeholder ? 'Yes' : 'No', 'Active' => fn ($r) => $r->active ? 'Yes' : 'No'],
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                ['name' => 'role', 'label' => 'Role', 'type' => 'text'],
                ['name' => 'bio', 'label' => 'Short introduction', 'type' => 'textarea', 'help' => 'Two or three sentences.'],
                ['name' => 'photo', 'label' => 'Photo (optional)', 'type' => 'image', 'alt' => 'photo_alt', 'help' => 'A photo of this person, with their permission. Without one a neutral avatar is shown.'],
                ['name' => 'sort', 'label' => 'Order (smaller shows first)', 'type' => 'number'],
                ['name' => 'is_placeholder', 'label' => 'This is placeholder text', 'type' => 'checkbox'],
                ['name' => 'active', 'label' => 'Show on the site', 'type' => 'checkbox'],
            ],
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'role' => ['nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:1200'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'photo_alt' => ['nullable', 'string', 'max:200', 'required_with:photo'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'is_placeholder' => ['nullable', 'boolean'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
