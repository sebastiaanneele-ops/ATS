<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\Vacancy;
use Illuminate\Http\JsonResponse;

class ApplicationController extends Controller
{
    /**
     * Neem een sollicitatie in ontvangst voor een gepubliceerde vacature.
     */
    public function store(StoreApplicationRequest $request, string $slug): JsonResponse
    {
        $vacancy = Vacancy::query()
            ->published()
            ->with('screeningQuestions')
            ->where('slug', $slug)
            ->firstOrFail();

        // Honeypot: bots vullen dit verborgen veld. Stilletjes "accepteren" zonder op te slaan.
        if (filled($request->input('company_website'))) {
            return response()->json(['message' => 'Bedankt voor je sollicitatie.'], 201);
        }

        $answers = (array) $request->input('answers', []);

        // Verplichte screeningvragen moeten beantwoord zijn.
        $errors = [];
        foreach ($vacancy->screeningQuestions as $question) {
            if ($question->is_required && blank($answers[$question->id] ?? null)) {
                $errors["answers.{$question->id}"] = ['Beantwoord deze vraag.'];
            }
        }

        if ($errors !== []) {
            return response()->json([
                'message' => 'Beantwoord alle verplichte vragen.',
                'errors' => $errors,
            ], 422);
        }

        $cvPath = null;
        $cvName = null;

        if ($request->hasFile('cv')) {
            $file = $request->file('cv');
            $cvName = $file->getClientOriginalName();
            // Private disk (storage/app/private) — niet publiek benaderbaar.
            $cvPath = $file->store('cvs', 'local');
        }

        $application = $vacancy->applications()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'motivation' => $request->validated('motivation'),
            'cv_path' => $cvPath,
            'cv_original_name' => $cvName,
            'status' => ApplicationStatus::New,
            'source' => 'website',
            'consent_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        $this->storeScreeningAnswers($application, $vacancy, $answers);

        return response()->json([
            'message' => 'Bedankt voor je sollicitatie.',
            'id' => $application->id,
        ], 201);
    }

    /**
     * Sla de screeningantwoorden op en wijs de sollicitatie automatisch af
     * wanneer een knock-outantwoord is gegeven.
     *
     * @param  array<int|string, mixed>  $answers
     */
    protected function storeScreeningAnswers(Application $application, Vacancy $vacancy, array $answers): void
    {
        if ($vacancy->screeningQuestions->isEmpty()) {
            return;
        }

        $knockedOut = false;

        foreach ($vacancy->screeningQuestions as $question) {
            $raw = $answers[$question->id] ?? null;
            $value = is_scalar($raw) ? trim((string) $raw) : null;

            $isKnockout = $question->isKnockout($value);
            $knockedOut = $knockedOut || $isKnockout;

            $application->screeningAnswers()->create([
                'screening_question_id' => $question->id,
                'question_label' => $question->label,
                'answer' => $value,
                'is_knockout' => $isKnockout,
            ]);
        }

        if ($knockedOut) {
            $application->update([
                'knocked_out' => true,
                'status' => ApplicationStatus::Rejected,
            ]);
        }
    }
}
