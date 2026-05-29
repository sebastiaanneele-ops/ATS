<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\PipelineStage;
use Illuminate\Database\Seeder;

class PipelineStageSeeder extends Seeder
{
    public function run(): void
    {
        if (PipelineStage::count() > 0) {
            return;
        }

        $stages = [
            ['name' => 'Nieuw', 'color' => 'gray', 'position' => 1, 'is_default' => true],
            ['name' => 'Screening', 'color' => 'info', 'position' => 2, 'is_default' => false],
            ['name' => 'Gesprek 1', 'color' => 'warning', 'position' => 3, 'is_default' => false],
            ['name' => 'Gesprek 2', 'color' => 'warning', 'position' => 4, 'is_default' => false],
            ['name' => 'Aanbod', 'color' => 'primary', 'position' => 5, 'is_default' => false],
            ['name' => 'Aangenomen', 'color' => 'success', 'position' => 6, 'is_default' => false],
            ['name' => 'Afgewezen', 'color' => 'danger', 'position' => 7, 'is_default' => false],
        ];

        foreach ($stages as $stage) {
            PipelineStage::create($stage);
        }

        // Bestaande sollicitaties zonder fase in de standaardfase plaatsen.
        $default = PipelineStage::default();
        if ($default) {
            Application::whereNull('pipeline_stage_id')->update(['pipeline_stage_id' => $default->id]);
        }
    }
}
