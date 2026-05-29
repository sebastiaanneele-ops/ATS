<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.ats.api_key' => 'test-key']);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jan Jansen',
            'email' => 'jan@example.com',
            'phone' => '0612345678',
            'motivation' => 'Ik ben enthousiast over deze functie.',
            'consent' => '1',
        ], $overrides);
    }

    public function test_can_submit_application_with_cv(): void
    {
        Storage::fake('local');
        $vacancy = Vacancy::factory()->create();

        $response = $this->withHeaders([
            'X-ATS-Key' => 'test-key',
            'Accept' => 'application/json',
        ])->post("/api/v1/vacancies/{$vacancy->slug}/applications", $this->payload([
            'cv' => UploadedFile::fake()->create('cv.pdf', 120, 'application/pdf'),
        ]));

        $response->assertCreated();
        $this->assertDatabaseHas('applications', [
            'vacancy_id' => $vacancy->id,
            'email' => 'jan@example.com',
            'status' => 'new',
        ]);

        $application = Application::firstOrFail();
        $this->assertNotNull($application->cv_path);
        $this->assertSame('cv.pdf', $application->cv_original_name);
        $this->assertNotNull($application->consent_at);
        Storage::disk('local')->assertExists($application->cv_path);
    }

    public function test_consent_is_required(): void
    {
        $vacancy = Vacancy::factory()->create();

        $this->withHeaders(['X-ATS-Key' => 'test-key', 'Accept' => 'application/json'])
            ->post("/api/v1/vacancies/{$vacancy->slug}/applications", $this->payload(['consent' => '0']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('consent');
    }

    public function test_api_key_is_required(): void
    {
        $vacancy = Vacancy::factory()->create();

        $this->withHeaders(['Accept' => 'application/json'])
            ->post("/api/v1/vacancies/{$vacancy->slug}/applications", $this->payload())
            ->assertStatus(401);
    }

    public function test_honeypot_is_silently_ignored(): void
    {
        $vacancy = Vacancy::factory()->create();

        $this->withHeaders(['X-ATS-Key' => 'test-key', 'Accept' => 'application/json'])
            ->post("/api/v1/vacancies/{$vacancy->slug}/applications", $this->payload([
                'company_website' => 'http://spam.example',
            ]))
            ->assertCreated();

        $this->assertDatabaseCount('applications', 0);
    }

    public function test_cannot_apply_to_unpublished_vacancy(): void
    {
        $draft = Vacancy::factory()->draft()->create();

        $this->withHeaders(['X-ATS-Key' => 'test-key', 'Accept' => 'application/json'])
            ->post("/api/v1/vacancies/{$draft->slug}/applications", $this->payload())
            ->assertNotFound();
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        Role::findOrCreate('super_admin', 'web');
        $user->assignRole('super_admin');

        return $user;
    }

    public function test_admin_can_view_application_pages(): void
    {
        $this->actingAs($this->superAdmin());
        $application = Application::factory()->create();

        $this->get('/admin/applications')->assertSuccessful();
        $this->get("/admin/applications/{$application->getKey()}/edit")->assertSuccessful();
    }

    public function test_admin_can_download_cv(): void
    {
        Storage::fake('local');
        $path = UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf')->store('cvs', 'local');
        $application = Application::factory()->create([
            'cv_path' => $path,
            'cv_original_name' => 'cv.pdf',
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.applications.cv', $application))
            ->assertOk();
    }

    public function test_cv_download_requires_authentication(): void
    {
        $application = Application::factory()->create(['cv_path' => 'cvs/whatever.pdf']);

        $this->get(route('admin.applications.cv', $application))->assertRedirect();
    }
}
