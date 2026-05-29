<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use App\Models\PipelineStage;
use App\Models\Vacancy;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $openVacatures = Vacancy::query()->published()->count();
        $totaal = Application::count();
        $nieuwDezeWeek = Application::where('created_at', '>=', now()->subDays(7))->count();

        $hiredStage = PipelineStage::where('name', 'Aangenomen')->first();
        $aangenomen = $hiredStage
            ? Application::where('pipeline_stage_id', $hiredStage->id)->count()
            : 0;

        $avgDays = null;
        if ($hiredStage) {
            $hired = Application::where('pipeline_stage_id', $hiredStage->id)->get(['created_at', 'updated_at']);
            if ($hired->isNotEmpty()) {
                $avgDays = round($hired->avg(
                    fn (Application $a) => abs($a->created_at->diffInDays($a->updated_at))
                ), 1);
            }
        }

        return [
            Stat::make('Openstaande vacatures', $openVacatures),
            Stat::make('Sollicitaties totaal', $totaal),
            Stat::make('Nieuw (laatste 7 dagen)', $nieuwDezeWeek),
            Stat::make('Aangenomen', $aangenomen),
            Stat::make('Gem. tijd tot aanname', $avgDays !== null ? $avgDays.' dagen' : '—')
                ->description('Indicatie op basis van laatste statuswijziging'),
        ];
    }
}
