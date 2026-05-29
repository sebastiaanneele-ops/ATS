<?php

namespace App\Filament\Widgets;

use App\Models\PipelineStage;
use Filament\Widgets\ChartWidget;

class ApplicationsPerStageChart extends ChartWidget
{
    protected ?string $heading = 'Sollicitaties per fase';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $stages = PipelineStage::query()
            ->orderBy('position')
            ->withCount('applications')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Sollicitaties',
                    'data' => $stages->pluck('applications_count')->all(),
                    'backgroundColor' => '#3b82f6',
                ],
            ],
            'labels' => $stages->pluck('name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
