<?php

namespace App\Support;

use App\Models\ApplicationStageLog;
use App\Models\PipelineStage;

class PipelineReport
{
    /**
     * Gemiddeld aantal dagen dat sollicitaties in elke fase doorbrengen
     * (alleen afgeronde fases — de huidige fase telt nog niet mee). Bottleneck-indicatie.
     *
     * @return array{labels: array<int, string>, days: array<int, float>}
     */
    public static function averageDaysPerStage(): array
    {
        $stages = PipelineStage::query()->orderBy('position')->get();

        $secondsPerStage = []; // stage_id => [seconds, ...]

        ApplicationStageLog::query()
            ->orderBy('application_id')
            ->orderBy('entered_at')
            ->get()
            ->groupBy('application_id')
            ->each(function ($logs) use (&$secondsPerStage) {
                $logs = $logs->values();

                foreach ($logs as $i => $log) {
                    $next = $logs[$i + 1] ?? null;
                    if ($next === null) {
                        continue; // huidige fase, nog niet doorlopen
                    }

                    $seconds = $next->entered_at->getTimestamp() - $log->entered_at->getTimestamp();
                    $secondsPerStage[$log->pipeline_stage_id][] = max(0, $seconds);
                }
            });

        $labels = [];
        $days = [];

        foreach ($stages as $stage) {
            $labels[] = $stage->name;
            $values = $secondsPerStage[$stage->id] ?? [];
            $avgSeconds = $values === [] ? 0 : array_sum($values) / count($values);
            $days[] = round($avgSeconds / 86400, 1);
        }

        return ['labels' => $labels, 'days' => $days];
    }

    /**
     * Hoeveel sollicitaties elke fase ooit hebben bereikt (conversie-funnel).
     *
     * @return array{labels: array<int, string>, counts: array<int, int>}
     */
    public static function funnelCounts(): array
    {
        $stages = PipelineStage::query()->orderBy('position')->get();

        $labels = [];
        $counts = [];

        foreach ($stages as $stage) {
            $labels[] = $stage->name;
            $counts[] = ApplicationStageLog::query()
                ->where('pipeline_stage_id', $stage->id)
                ->distinct()
                ->count('application_id');
        }

        return ['labels' => $labels, 'counts' => $counts];
    }
}
