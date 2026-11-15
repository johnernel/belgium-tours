<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizOption extends Model
{
    protected $fillable = ['quiz_step_id', 'label', 'value', 'icon', 'sort', 'active'];

    public function step(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(QuizStep::class, 'quiz_step_id');
    }

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}
