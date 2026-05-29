<?php

namespace App\Models;

use App\Enums\EmploymentType;
use App\Enums\VacancyStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Vacancy extends Model
{
    /** @use HasFactory<\Database\Factories\VacancyFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'department',
        'location',
        'employment_type',
        'hours',
        'description',
        'requirements',
        'salary_min',
        'salary_max',
        'status',
        'published_at',
        'closes_at',
        'apply_email',
    ];

    protected function casts(): array
    {
        return [
            'status' => VacancyStatus::class,
            'employment_type' => EmploymentType::class,
            'salary_min' => 'integer',
            'salary_max' => 'integer',
            'published_at' => 'datetime',
            'closes_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Vacancy $vacancy): void {
            if (blank($vacancy->slug) && filled($vacancy->title)) {
                $vacancy->slug = static::uniqueSlug($vacancy->title, $vacancy->id);
            }
        });
    }

    protected static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn (Builder $q) => $q->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    /**
     * Alleen vacatures die publiek zichtbaar moeten zijn:
     * gepubliceerd, publicatiedatum bereikt en (nog) niet gesloten.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', VacancyStatus::Published->value)
            ->where(function (Builder $q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->where(function (Builder $q) {
                $q->whereNull('closes_at')->orWhereDate('closes_at', '>=', now()->toDateString());
            });
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function isPublic(): bool
    {
        return $this->status === VacancyStatus::Published
            && (is_null($this->published_at) || $this->published_at->lte(now()))
            && (is_null($this->closes_at) || $this->closes_at->gte(now()->startOfDay()));
    }
}
