<?php

namespace Database\Factories;

use App\Enums\EmploymentType;
use App\Enums\VacancyStatus;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Vacancy>
 */
class VacancyFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->randomElement([
            'Medewerker Klantenservice',
            'Front-end Developer',
            'Online Marketeer',
            'Accountmanager Binnendienst',
            'Financieel Administratief Medewerker',
            'UX/UI Designer',
            'Projectmanager',
            'HR-adviseur',
        ]).' ('.fake()->randomElement(['m/v', 'm/v/x']).')';

        $min = fake()->numberBetween(2500, 3500);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'department' => fake()->randomElement(['Sales', 'Marketing', 'IT', 'Finance', 'HR', 'Operations']),
            'location' => fake()->randomElement(['Amsterdam', 'Rotterdam', 'Utrecht', 'Eindhoven', 'Zwolle', 'Remote']),
            'employment_type' => fake()->randomElement(EmploymentType::cases()),
            'hours' => fake()->randomElement(['32-40 uur', '24-32 uur', '40 uur', '16-24 uur']),
            'description' => '<p>'.implode('</p><p>', fake()->paragraphs(3)).'</p>',
            'requirements' => '<ul><li>'.implode('</li><li>', [
                'Relevante werkervaring',
                'Goede communicatieve vaardigheden',
                'Teamspeler met eigen initiatief',
                'Uitstekende beheersing van de Nederlandse taal',
            ]).'</li></ul>',
            'salary_min' => $min,
            'salary_max' => $min + fake()->numberBetween(500, 1500),
            'status' => VacancyStatus::Published,
            'published_at' => now()->subDays(fake()->numberBetween(0, 20)),
            'closes_at' => now()->addDays(fake()->numberBetween(14, 60)),
            'apply_email' => 'vacatures@personeelpartners.nl',
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => VacancyStatus::Draft,
            'published_at' => null,
        ]);
    }
}
