<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScreeningQuestion extends Model
{
    public const TYPE_BOOLEAN = 'boolean';

    public const TYPE_CHOICE = 'choice';

    public const TYPE_TEXT = 'text';

    protected $fillable = [
        'vacancy_id',
        'label',
        'type',
        'options',
        'knockout_values',
        'is_required',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'knockout_values' => 'array',
            'is_required' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ScreeningAnswer::class);
    }

    /**
     * De mogelijke antwoorden voor deze vraag (voor de UI en knock-outkeuze).
     *
     * @return array<int, string>
     */
    public function answerOptions(): array
    {
        return match ($this->type) {
            self::TYPE_BOOLEAN => ['Ja', 'Nee'],
            self::TYPE_CHOICE => $this->options ?? [],
            default => [],
        };
    }

    /**
     * Is dit antwoord een knock-out (leidt tot automatische afwijzing)?
     */
    public function isKnockout(?string $answer): bool
    {
        if (blank($answer) || blank($this->knockout_values)) {
            return false;
        }

        return in_array($answer, $this->knockout_values, true);
    }
}
