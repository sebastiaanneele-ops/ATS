<?php

namespace Tests\Feature;

use App\Filament\Pages\PipelineBoard;
use App\Models\Application;
use App\Models\PipelineStage;
use App\Models\User;
use App\Models\Vacancy;
use Database\Seeders\PipelineStageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PipelineTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        Role::findOrCreate('super_admin', 'web');
        $user->assignRole('super_admin');

        return $user;
    }

    public function test_new_application_gets_default_stage(): void
    {
        $this->seed(PipelineStageSeeder::class);
        $application = Application::factory()->create();

        $this->assertNotNull($application->pipeline_stage_id);
        $this->assertTrue($application->stage->is_default);
        $this->assertSame('Nieuw', $application->stage->name);
    }

    public function test_board_moves_application_between_stages(): void
    {
        $this->seed(PipelineStageSeeder::class);
        $application = Application::factory()->create();

        $first = PipelineStage::orderBy('position')->first();
        $second = PipelineStage::orderBy('position')->skip(1)->first();

        $this->assertSame($first->id, $application->pipeline_stage_id);

        $board = new PipelineBoard();
        $board->moveForward($application->id);
        $this->assertSame($second->id, $application->fresh()->pipeline_stage_id);

        $board->moveBack($application->id);
        $this->assertSame($first->id, $application->fresh()->pipeline_stage_id);

        // Mag niet vóór de eerste fase komen.
        $board->moveBack($application->id);
        $this->assertSame($first->id, $application->fresh()->pipeline_stage_id);
    }

    public function test_note_records_the_authenticated_author(): void
    {
        $user = $this->superAdmin();
        $this->actingAs($user);

        $application = Application::factory()->create();
        $note = $application->notes()->create(['body' => 'Sterke kandidaat.']);

        $this->assertSame($user->id, $note->user_id);
    }

    public function test_average_score_is_computed(): void
    {
        $application = Application::factory()->create();
        $application->evaluations()->create(['score' => 4]);
        $application->evaluations()->create(['score' => 2]);

        $this->assertSame(3.0, $application->averageScore());
    }

    public function test_admin_can_view_pipeline_pages(): void
    {
        $this->seed(PipelineStageSeeder::class);
        $this->actingAs($this->superAdmin());
        $application = Application::factory()->create();

        $this->get('/admin/pipeline-board')->assertSuccessful();
        $this->get('/admin/pipeline-stages')->assertSuccessful();
        $this->get("/admin/applications/{$application->getKey()}/edit")->assertSuccessful();
    }
}
