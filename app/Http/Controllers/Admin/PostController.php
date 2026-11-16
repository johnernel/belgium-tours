<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class PostController extends CrudController
{
    protected string $model = Post::class;

    protected string $routeBase = 'admin.posts';

    protected function orderBy(): string
    {
        return 'id'; // newest first, see orderDirection()
    }

    protected function orderDirection(): string
    {
        return 'desc';
    }

    protected function newRecord(): Model
    {
        return new Post(['active' => true, 'is_placeholder' => false, 'read_minutes' => 4]);
    }

    protected function config(): array
    {
        return [
            'title' => 'Journal', 'singular' => 'Article',
            'help' => 'Articles for the home page "Stories" section and the Journal page. Sample articles are placeholders: replace them with real ones and untick "placeholder" (placeholders are hidden from Google).',
            'columns' => ['Title' => 'title', 'Category' => 'category', 'Published' => fn ($r) => $r->published_at?->format('j M Y') ?? 'Now', 'Placeholder' => fn ($r) => $r->is_placeholder ? 'Yes' : 'No', 'Active' => fn ($r) => $r->active ? 'Yes' : 'No'],
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true],
                ['name' => 'slug', 'label' => 'URL name', 'type' => 'text', 'help' => 'Leave blank to create it from the title.'],
                ['name' => 'category', 'label' => 'Category tag', 'type' => 'text', 'help' => 'One or two words, e.g. City notes.'],
                ['name' => 'excerpt', 'label' => 'Short excerpt', 'type' => 'textarea', 'help' => 'One or two sentences shown on the card.'],
                ['name' => 'body', 'label' => 'Article', 'type' => 'rich'],
                ['name' => 'image', 'label' => 'Photo', 'type' => 'image', 'alt' => 'image_alt', 'help' => 'Only photos you are free to use.'],
                ['name' => 'read_minutes', 'label' => 'Minutes to read', 'type' => 'number'],
                ['name' => 'published_at', 'label' => 'Publish date (YYYY-MM-DD, blank = now)', 'type' => 'text'],
                ['name' => 'is_placeholder', 'label' => 'This is placeholder text', 'type' => 'checkbox'],
                ['name' => 'active', 'label' => 'Show on the site', 'type' => 'checkbox'],
            ],
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'alpha_dash', 'max:160', Rule::unique('posts', 'slug')->ignore($record?->id)],
            'category' => ['nullable', 'string', 'max:60'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string', 'max:60000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'image_alt' => ['nullable', 'string', 'max:200', 'required_with:image'],
            'read_minutes' => ['nullable', 'integer', 'min:1', 'max:60'],
            'published_at' => ['nullable', 'date'],
            'is_placeholder' => ['nullable', 'boolean'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
