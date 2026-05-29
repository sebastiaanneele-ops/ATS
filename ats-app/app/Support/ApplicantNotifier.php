<?php

namespace App\Support;

use App\Mail\StageNotificationMail;
use App\Models\Application;
use App\Models\EmailLog;
use App\Models\PipelineStage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ApplicantNotifier
{
    /**
     * Verstuur (en log) de fase-e-mail naar de kandidaat, als de fase dat vereist.
     */
    public static function notify(Application $application, ?PipelineStage $stage): void
    {
        if (! $stage || ! $stage->notifiesApplicant() || blank($application->email)) {
            return;
        }

        $replacements = [
            '{{naam}}' => $application->name,
            '{{vacature}}' => $application->vacancy?->title ?? '',
            '{{fase}}' => $stage->name,
        ];

        $subject = strtr($stage->email_subject, $replacements);
        $body = strtr($stage->email_body, $replacements);

        try {
            Mail::to($application->email)->send(new StageNotificationMail($subject, $body));

            EmailLog::create([
                'application_id' => $application->id,
                'pipeline_stage_id' => $stage->id,
                'to' => $application->email,
                'subject' => $subject,
                'body' => $body,
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('ATS: versturen fase-e-mail mislukt', [
                'application_id' => $application->id,
                'stage_id' => $stage->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
