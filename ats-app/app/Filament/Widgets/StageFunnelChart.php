<?php

namespace App\Filament\Widgets;

use App\Support\PipelineReport;
use Filament\Widgets\ChartWidget;

class StageFunnelChart extends ChartWidget
{
    protected ?string $heading = 'Funnel: kandidaten per fase bereikt';

    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $report = PipelineReport::funnelCounts();

        return [
            'datasets' => [
                [
                    'label' => 'Aantal kandidaten',
                    'data' => $report['counts'],
                    'backgroundColor' => '#8b5cf6',
                ],
            ],
            'labels' => $report['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * Horizontale balken lezen prettiger voor een funnel.
     */
    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
        ];
    }
}
