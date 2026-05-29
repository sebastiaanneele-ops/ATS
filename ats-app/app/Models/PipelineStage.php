<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineStage extends Model
{
    protected $fillable = [
        'name',
        'color',
        'position',
        'is_default',
        'notify_applicant',
        'email_subject',
        'email_body',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'notify_applicant' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function notifiesApplicant(): bool
    {
        return $this->notify_applicant
            && filled($this->email_subject)
            && filled($this->email_body);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * De standaardfase voor nieuwe sollicitaties (of anders de eerste fase).
     */
    public static function default(): ?self
    {
        return static::query()->where('is_default', true)->orderBy('position')->first()
            ?? static::query()->orderBy('position')->first();
    }
}
