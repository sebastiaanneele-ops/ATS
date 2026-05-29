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
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'position' => 'integer',
        ];
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
