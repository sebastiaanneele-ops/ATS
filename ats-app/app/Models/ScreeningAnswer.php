<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreeningAnswer extends Model
{
    protected $fillable = [
        'application_id',
        'screening_question_id',
        'question_label',
        'answer',
        'is_knockout',
    ];

    protected function casts(): array
    {
        return [
            'is_knockout' => 'boolean',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ScreeningQuestion::class, 'screening_question_id');
    }
}
