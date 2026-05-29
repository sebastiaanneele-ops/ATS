<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@ism.test'],
            ['name' => 'Admin', 'password' => 'password'],
        );

        $this->call(PipelineStageSeeder::class);

        if (Vacancy::count() === 0) {
            Vacancy::factory()->count(5)->create();
            Vacancy::factory()->count(2)->draft()->create();
        }
    }
}
