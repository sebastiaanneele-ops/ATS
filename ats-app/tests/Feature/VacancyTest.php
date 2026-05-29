<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VacancyTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        Role::findOrCreate('super_admin', 'web');
        $user->assignRole('super_admin');

        return $user;
    }

    public function test_api_lists_only_published_vacancies(): void
    {
        Vacancy::factory()->count(3)->create();
        Vacancy::factory()->count(2)->draft()->create();

        $response = $this->getJson('/api/v1/vacancies');

        $response->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_api_shows_a_published_vacancy_by_slug(): void
    {
        $vacancy = Vacancy::factory()->create();

        $this->getJson("/api/v1/vacancies/{$vacancy->slug}")
            ->assertOk()
            ->assertJsonPath('data.slug', $vacancy->slug)
            ->assertJsonPath('data.title', $vacancy->title);
    }

    public function test_api_hides_draft_vacancies(): void
    {
        $draft = Vacancy::factory()->draft()->create();

        $this->getJson("/api/v1/vacancies/{$draft->slug}")->assertNotFound();
    }

    public function test_slug_is_generated_from_title_when_blank(): void
    {
        $vacancy = Vacancy::create([
            'title' => 'Senior Back-end Developer',
            'status' => 'draft',
        ]);

        $this->assertSame('senior-back-end-developer', $vacancy->slug);
    }

    public function test_super_admin_can_view_vacancy_admin_pages(): void
    {
        $this->actingAs($this->superAdmin());
        $vacancy = Vacancy::factory()->create();

        $this->get('/admin/vacancies')->assertSuccessful();
        $this->get('/admin/vacancies/create')->assertSuccessful();
        $this->get("/admin/vacancies/{$vacancy->getKey()}/edit")->assertSuccessful();
    }
}
