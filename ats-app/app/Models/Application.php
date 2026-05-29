<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    /** @use HasFactory<\Database\Factories\ApplicationFactory> */
    use HasFactory;

    protected $fillable = [
        'vacancy_id',
        'pipeline_stage_id',
        'name',
        'email',
        'phone',
        'motivation',
        'cv_path',
        'cv_original_name',
        'status',
        'source',
        'consent_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'consent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Nieuwe sollicitaties starten in de standaardfase (indien geconfigureerd).
        static::creating(function (Application $application): void {
            if (blank($application->pipeline_stage_id)) {
                $application->pipeline_stage_id = PipelineStage::default()?->id;
            }
        });
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ApplicationNote::class)->latest();
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function hasCv(): bool
    {
        return filled($this->cv_path);
    }

    public function averageScore(): ?float
    {
        $avg = $this->evaluations()->avg('score');

        return $avg !== null ? round((float) $avg, 1) : null;
    }
}
