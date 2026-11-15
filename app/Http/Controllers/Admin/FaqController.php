<?php

namespace App\Http\Controllers\Admin;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Model;

class FaqController extends CrudController
{
    protected string $model = Faq::class;

    protected string $routeBase = 'admin.faqs';

    protected function config(): array
    {
        return [
            'title' => 'FAQs', 'singular' => 'FAQ',
            'help' => 'Shown on the home page and added to Google as FAQ structured data.',
            'columns' => ['Question' => 'question', 'Active' => fn ($r) => $r->active ? 'Yes' : 'No'],
            'fields' => [
                ['name' => 'question', 'label' => 'Question', 'type' => 'text', 'required' => true],
                ['name' => 'answer', 'label' => 'Answer', 'type' => 'textarea', 'required' => true],
                ['name' => 'sort', 'label' => 'Order', 'type' => 'number'],
                ['name' => 'active', 'label' => 'Show on the site', 'type' => 'checkbox'],
            ],
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:2000'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
