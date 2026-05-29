<?php

namespace App\Http\Resources;

use App\Models\Vacancy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Vacancy
 */
class VacancyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'department' => $this->department,
            'location' => $this->location,
            'employment_type' => $this->employment_type?->value,
            'employment_type_label' => $this->employment_type?->getLabel(),
            'hours' => $this->hours,
            'salary' => [
                'min' => $this->salary_min,
                'max' => $this->salary_max,
                'formatted' => $this->formattedSalary(),
            ],
            'description' => $this->description,
            'requirements' => $this->requirements,
            'apply_email' => $this->apply_email,
            'published_at' => $this->published_at?->toIso8601String(),
            'closes_at' => $this->closes_at?->toDateString(),
        ];
    }

    protected function formattedSalary(): ?string
    {
        if (! $this->salary_min && ! $this->salary_max) {
            return null;
        }

        $fmt = fn (int $n): string => '€ '.number_format($n, 0, ',', '.');

        if ($this->salary_min && $this->salary_max) {
            return $fmt($this->salary_min).' - '.$fmt($this->salary_max).' per maand';
        }

        return $fmt($this->salary_min ?? $this->salary_max).' per maand';
    }
}
