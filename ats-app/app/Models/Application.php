<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Support\ApplicantNotifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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
        'knocked_out',
        'source',
        'consent_at',
        'ip_address',
        'anonymized_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'knocked_out' => 'boolean',
            'consent_at' => 'datetime',
            'anonymized_at' => 'datetime',
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

        // Stuur de fase-e-mail bij binnenkomst (ontvangstbevestiging) en bij fasewisseling.
        static::created(function (Application $application): void {
            ApplicantNotifier::notify($application, $application->stage()->first());
        });

        static::updated(function (Application $application): void {
            if ($application->wasChanged('pipeline_stage_id')) {
                ApplicantNotifier::notify($application, $application->stage()->first());
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

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class)->latest();
    }

    public function screeningAnswers(): HasMany
    {
        return $this->hasMany(ScreeningAnswer::class);
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

    public function isAnonymized(): bool
    {
        return filled($this->anonymized_at);
    }

    /**
     * Sollicitaties waarvan de bewaartermijn is verstreken en die nog niet
     * geanonimiseerd zijn.
     */
    public function scopeDueForRetention(Builder $query, int $days): Builder
    {
        return $query
            ->whereNull('anonymized_at')
            ->where('created_at', '<=', now()->subDays($days));
    }

    /**
     * Verwijder persoonsgegevens (AVG): CV weg, gegevens gewist, notities en
     * e-maillogs verwijderd, beoordelings-toelichtingen geleegd. Niet-persoonlijke
     * velden (vacature, fase, datums) blijven bewaard voor statistiek.
     */
    public function anonymize(): void
    {
        if ($this->cv_path && Storage::disk('local')->exists($this->cv_path)) {
            Storage::disk('local')->delete($this->cv_path);
        }

        $this->notes()->delete();
        $this->emailLogs()->delete();
        $this->evaluations()->update(['comment' => null]);

        $this->forceFill([
            'name' => 'Geanonimiseerd',
            'email' => 'verwijderd-'.$this->getKey().'@example.invalid',
            'phone' => null,
            'motivation' => null,
            'cv_path' => null,
            'cv_original_name' => null,
            'ip_address' => null,
            'anonymized_at' => now(),
        ])->saveQuietly();
    }
}
