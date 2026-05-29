<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use App\Models\Vacancy;
use Database\Seeders\PipelineStageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        Role::findOrCreate('super_admin', 'web');
        $user->assignRole('super_admin');

        return $user;
    }

    public function test_dashboard_with_widgets_renders_for_admin(): void
    {
        $this->seed(PipelineStageSeeder::class);
        $vacancy = Vacancy::factory()->create();
        Application::factory()->count(3)->create(['vacancy_id' => $vacancy->id]);

        $this->actingAs($this->superAdmin())
            ->get('/admin')
            ->assertSuccessful();
    }
}
