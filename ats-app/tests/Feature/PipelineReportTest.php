<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\PipelineStage;
use App\Support\PipelineReport;
use Database\Seeders\PipelineStageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PipelineReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PipelineStageSeeder::class);
    }

    public function test_stage_entry_is_logged_on_create_and_on_change(): void
    {
        $application = Application::factory()->create();
        $screening = PipelineStage::where('name', 'Screening')->firstOrFail();

        $application->update(['pipeline_stage_id' => $screening->id]);

        $this->assertSame(2, $application->stageLogs()->count());
    }

    public function test_average_days_per_stage_is_computed_from_transitions(): void
    {
        $screening = PipelineStage::where('name', 'Screening')->firstOrFail();
        $gesprek = PipelineStage::where('name', 'Gesprek 1')->firstOrFail();

        $application = Application::factory()->create(); // fase Nieuw op t=0

        $this->travel(2)->days();
        $application->update(['pipeline_stage_id' => $screening->id]);

        $this->travel(3)->days();
        $application->update(['pipeline_stage_id' => $gesprek->id]);

        $report = PipelineReport::averageDaysPerStage();
        $days = array_combine($report['labels'], $report['days']);

        $this->assertEqualsWithDelta(2.0, $days['Nieuw'], 0.1);
        $this->assertEqualsWithDelta(3.0, $days['Screening'], 0.1);
        $this->assertSame(0.0, $days['Gesprek 1']); // huidige fase, nog niet doorlopen

        $this->travelBack();
    }

    public function test_funnel_counts_distinct_applications_per_reached_stage(): void
    {
        $screening = PipelineStage::where('name', 'Screening')->firstOrFail();

        $a = Application::factory()->create();
        $a->update(['pipeline_stage_id' => $screening->id]);

        Application::factory()->create(); // blijft in Nieuw

        $report = PipelineReport::funnelCounts();
        $counts = array_combine($report['labels'], $report['counts']);

        $this->assertSame(2, $counts['Nieuw']);
        $this->assertSame(1, $counts['Screening']);
        $this->assertSame(0, $counts['Gesprek 1']);
    }
}
