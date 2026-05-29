<?php

namespace Tests\Feature;

use App\Mail\StageNotificationMail;
use App\Models\Application;
use App\Models\PipelineStage;
use App\Models\Vacancy;
use Database\Seeders\PipelineStageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PipelineStageSeeder::class);
    }

    public function test_confirmation_email_is_sent_and_logged_on_apply(): void
    {
        Mail::fake();

        $vacancy = Vacancy::factory()->create();
        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'email' => 'kandidaat@example.com',
        ]);

        Mail::assertSent(
            StageNotificationMail::class,
            fn (StageNotificationMail $mail) => $mail->hasTo('kandidaat@example.com')
        );

        $this->assertDatabaseHas('email_logs', [
            'application_id' => $application->id,
            'to' => 'kandidaat@example.com',
        ]);
    }

    public function test_no_email_for_stage_without_template(): void
    {
        Mail::fake();

        $application = Application::factory()->create(); // 1x: bevestiging (Nieuw)
        $screening = PipelineStage::where('name', 'Screening')->firstOrFail();

        $application->update(['pipeline_stage_id' => $screening->id]);

        // Geen extra mail voor Screening (geen sjabloon).
        Mail::assertSent(StageNotificationMail::class, 1);
    }

    public function test_rejection_email_on_move_to_rejected_stage(): void
    {
        Mail::fake();

        $application = Application::factory()->create(); // 1x: bevestiging
        $rejected = PipelineStage::where('name', 'Afgewezen')->firstOrFail();

        $application->update(['pipeline_stage_id' => $rejected->id]);

        Mail::assertSent(StageNotificationMail::class, 2);
    }

    public function test_placeholders_are_replaced(): void
    {
        Mail::fake();

        $vacancy = Vacancy::factory()->create(['title' => 'Test Functie XYZ']);
        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'name' => 'Jan Tester',
        ]);

        Mail::assertSent(StageNotificationMail::class, function (StageNotificationMail $mail) {
            return str_contains($mail->bodyHtml, 'Jan Tester')
                && str_contains($mail->bodyHtml, 'Test Functie XYZ')
                && ! str_contains($mail->bodyHtml, '{{');
        });
    }
}
