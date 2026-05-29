<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vacancy_id' => Vacancy::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'motivation' => fake()->optional()->paragraph(),
            'cv_path' => null,
            'cv_original_name' => null,
            'status' => ApplicationStatus::New,
            'source' => 'website',
            'consent_at' => now(),
            'ip_address' => fake()->ipv4(),
        ];
    }
}
