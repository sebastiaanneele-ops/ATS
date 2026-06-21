<?php

namespace Tests\Feature;

use App\Models\ScreeningQuestion;
use App\Models\User;
use App\Models\Vacancy;
use Database\Seeders\PipelineStageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ScreeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.ats.api_key' => 'test-key']);
    }

    private function vacancyWithKnockout(): Vacancy
    {
        $vacancy = Vacancy::factory()->create();
        ScreeningQuestion::create([
            'vacancy_id' => $vacancy->id,
            'label' => 'Heb je een rijbewijs B?',
            'type' => ScreeningQuestion::TYPE_BOOLEAN,
            'knockout_values' => ['Nee'],
            'is_required' => true,
            'position' => 1,
        ]);

        return $vacancy;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jan Jansen',
            'email' => 'jan@example.com',
            'consent' => '1',
        ], $overrides);
    }

    private function headers(): array
    {
        return ['X-ATS-Key' => 'test-key', 'Accept' => 'application/json'];
    }

    public function test_api_detail_exposes_screening_questions_without_knockout_values(): void
    {
        $vacancy = $this->vacancyWithKnockout();

        $this->getJson("/api/v1/vacancies/{$vacancy->slug}")
            ->assertOk()
            ->assertJsonPath('data.screening_questions.0.label', 'Heb je een rijbewijs B?')
            ->assertJsonPath('data.screening_questions.0.type', 'boolean')
            ->assertJsonPath('data.screening_questions.0.options', ['Ja', 'Nee'])
            ->assertJsonMissingPath('data.screening_questions.0.knockout_values');
    }

    public function test_answers_are_stored_with_the_application(): void
    {
        $vacancy = $this->vacancyWithKnockout();
        $questionId = $vacancy->screeningQuestions->first()->id;

        $this->withHeaders($this->headers())
            ->post("/api/v1/vacancies/{$vacancy->slug}/applications", $this->payload([
                'answers' => [$questionId => 'Ja'],
            ]))
            ->assertCreated();

        $this->assertDatabaseHas('screening_answers', [
            'question_label' => 'Heb je een rijbewijs B?',
            'answer' => 'Ja',
            'is_knockout' => false,
        ]);
        $this->assertDatabaseHas('applications', ['email' => 'jan@example.com', 'knocked_out' => false]);
    }

    public function test_knockout_answer_auto_rejects_the_application(): void
    {
        $this->seed(PipelineStageSeeder::class);
        $vacancy = $this->vacancyWithKnockout();
        $questionId = $vacancy->screeningQuestions->first()->id;

        $this->withHeaders($this->headers())
            ->post("/api/v1/vacancies/{$vacancy->slug}/applications", $this->payload([
                'answers' => [$questionId => 'Nee'],
            ]))
            ->assertCreated();

        $this->assertDatabaseHas('applications', [
            'email' => 'jan@example.com',
            'knocked_out' => true,
            'status' => 'rejected',
        ]);
        $this->assertDatabaseHas('screening_answers', [
            'answer' => 'Nee',
            'is_knockout' => true,
        ]);
    }

    public function test_required_question_must_be_answered(): void
    {
        $vacancy = $this->vacancyWithKnockout();

        $this->withHeaders($this->headers())
            ->post("/api/v1/vacancies/{$vacancy->slug}/applications", $this->payload())
            ->assertStatus(422);

        $this->assertDatabaseCount('applications', 0);
    }

    public function test_admin_can_view_screening_relation_managers(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate('super_admin', 'web');
        $user->assignRole('super_admin');
        $this->actingAs($user);

        $vacancy = $this->vacancyWithKnockout();

        $this->get("/admin/vacancies/{$vacancy->getKey()}/edit")->assertSuccessful();
    }
}
