<?php

namespace App\Filament\Widgets;

use App\Models\Vacancy;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Str;

class ApplicationsPerVacancyChart extends ChartWidget
{
    protected ?string $heading = 'Sollicitaties per vacature (top 10)';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $vacancies = Vacancy::query()
            ->withCount('applications')
            ->get()
            ->where('applications_count', '>', 0)
            ->sortByDesc('applications_count')
            ->take(10)
            ->values();

        return [
            'datasets' => [
                [
                    'label' => 'Sollicitaties',
                    'data' => $vacancies->pluck('applications_count')->all(),
                    'backgroundColor' => '#10b981',
                ],
            ],
            'labels' => $vacancies->pluck('title')->map(fn ($t) => Str::limit($t, 30))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
