<?php

namespace App\Filament\Pages;

use App\Models\Application;
use App\Models\PipelineStage;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class PipelineBoard extends Page
{
    protected string $view = 'filament.pages.pipeline-board';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedViewColumns;

    protected static string|UnitEnum|null $navigationGroup = 'Werving';

    protected static ?string $navigationLabel = 'Pipeline';

    protected static ?string $title = 'Pipeline';

    protected static ?int $navigationSort = 3;

    /**
     * Fases met hun sollicitaties (nieuwste eerst).
     *
     * @return Collection<int, PipelineStage>
     */
    public function stages(): Collection
    {
        return PipelineStage::query()
            ->orderBy('position')
            ->with([
                'applications' => fn ($query) => $query->with('vacancy')->latest(),
            ])
            ->get();
    }

    public function moveForward(int $applicationId): void
    {
        $this->move($applicationId, 1);
    }

    public function moveBack(int $applicationId): void
    {
        $this->move($applicationId, -1);
    }

    protected function move(int $applicationId, int $direction): void
    {
        $application = Application::find($applicationId);

        if (! $application) {
            return;
        }

        $stages = PipelineStage::query()->orderBy('position')->get();

        $currentIndex = $application->pipeline_stage_id
            ? $stages->search(fn (PipelineStage $stage) => $stage->id === $application->pipeline_stage_id)
            : -1;

        $newIndex = $currentIndex + $direction;

        if ($newIndex < 0 || $newIndex >= $stages->count()) {
            return;
        }

        $application->pipeline_stage_id = $stages[$newIndex]->id;
        $application->save();
    }
}
