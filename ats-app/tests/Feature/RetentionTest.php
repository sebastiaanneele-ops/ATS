<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\EmailLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RetentionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['ats.retention_days' => 30]);
    }

    public function test_old_application_is_anonymized(): void
    {
        Storage::fake('local');
        $cvPath = UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf')->store('cvs', 'local');

        $application = Application::factory()->create([
            'name' => 'Jan Jansen',
            'email' => 'jan@example.com',
            'phone' => '0612345678',
            'motivation' => 'Mijn motivatie.',
            'cv_path' => $cvPath,
            'cv_original_name' => 'cv.pdf',
            'ip_address' => '1.2.3.4',
            'created_at' => now()->subDays(40),
        ]);
        $application->notes()->create(['body' => 'Interne notitie']);
        $application->evaluations()->create(['score' => 4, 'comment' => 'Sterk']);
        EmailLog::create([
            'application_id' => $application->id,
            'to' => 'jan@example.com',
            'subject' => 'Onderwerp',
            'body' => 'Bericht',
            'sent_at' => now(),
        ]);

        $this->artisan('ats:anonymize-applications')->assertSuccessful();

        $application->refresh();
        $this->assertNotNull($application->anonymized_at);
        $this->assertSame('Geanonimiseerd', $application->name);
        $this->assertNull($application->cv_path);
        $this->assertNull($application->motivation);
        $this->assertNull($application->ip_address);
        $this->assertStringNotContainsString('jan@example.com', $application->email);

        Storage::disk('local')->assertMissing($cvPath);
        $this->assertSame(0, $application->notes()->count());
        $this->assertSame(0, $application->emailLogs()->count());

        // Beoordeling blijft (score) maar zonder toelichting.
        $evaluation = $application->evaluations()->first();
        $this->assertSame(4, $evaluation->score);
        $this->assertNull($evaluation->comment);
    }

    public function test_recent_application_is_kept(): void
    {
        $application = Application::factory()->create([
            'name' => 'Recente Kandidaat',
            'created_at' => now()->subDays(5),
        ]);

        $this->artisan('ats:anonymize-applications')->assertSuccessful();

        $application->refresh();
        $this->assertNull($application->anonymized_at);
        $this->assertSame('Recente Kandidaat', $application->name);
    }

    public function test_dry_run_changes_nothing(): void
    {
        $application = Application::factory()->create([
            'name' => 'Oude Kandidaat',
            'created_at' => now()->subDays(40),
        ]);

        $this->artisan('ats:anonymize-applications', ['--dry-run' => true])->assertSuccessful();

        $application->refresh();
        $this->assertNull($application->anonymized_at);
        $this->assertSame('Oude Kandidaat', $application->name);
    }
}
