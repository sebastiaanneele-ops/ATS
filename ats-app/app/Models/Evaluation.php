<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Evaluation extends Model
{
    protected $fillable = [
        'application_id',
        'user_id',
        'score',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Evaluation $evaluation): void {
            if (blank($evaluation->user_id) && Auth::check()) {
                $evaluation->user_id = Auth::id();
            }
        });
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
