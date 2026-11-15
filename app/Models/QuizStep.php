<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizStep extends Model
{
    protected $fillable = ['key', 'question', 'type', 'icon', 'sort', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuizOption::class)->orderBy('sort');
    }

    public function activeOptions(): HasMany
    {
        return $this->options()->where('active', true);
    }

    /** Active steps with active options, shaped for the Alpine quiz. */
    public static function forQuiz(): array
    {
        return static::where('active', true)->orderBy('sort')->with('activeOptions')->get()
            ->filter(fn ($s) => $s->activeOptions->isNotEmpty())
            ->map(fn ($s) => [
                'key' => $s->key,
                'question' => $s->question,
                'type' => $s->type,
                'options' => $s->activeOptions->map(fn ($o) => [
                    'label' => $o->label, 'value' => $o->value, 'icon' => $o->icon,
                ])->values()->all(),
            ])->values()->all();
    }
}
