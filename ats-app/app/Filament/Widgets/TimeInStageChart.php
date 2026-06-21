<?php

namespace App\Filament\Widgets;

use App\Support\PipelineReport;
use Filament\Widgets\ChartWidget;

class TimeInStageChart extends ChartWidget
{
    protected ?string $heading = 'Gemiddelde doorlooptijd per fase (dagen)';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $report = PipelineReport::averageDaysPerStage();

        return [
            'datasets' => [
                [
                    'label' => 'Gemiddeld aantal dagen',
                    'data' => $report['days'],
                    'backgroundColor' => '#f59e0b',
                ],
            ],
            'labels' => $report['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
