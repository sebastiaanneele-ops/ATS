<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ApplicationNote extends Model
{
    protected $fillable = [
        'application_id',
        'user_id',
        'body',
    ];

    protected static function booted(): void
    {
        static::creating(function (ApplicationNote $note): void {
            if (blank($note->user_id) && Auth::check()) {
                $note->user_id = Auth::id();
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
