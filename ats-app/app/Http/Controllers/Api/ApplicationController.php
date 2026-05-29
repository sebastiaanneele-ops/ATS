<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationRequest;
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
            ->where('slug', $slug)
            ->firstOrFail();

        // Honeypot: bots vullen dit verborgen veld. Stilletjes "accepteren" zonder op te slaan.
        if (filled($request->input('company_website'))) {
            return response()->json(['message' => 'Bedankt voor je sollicitatie.'], 201);
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

        return response()->json([
            'message' => 'Bedankt voor je sollicitatie.',
            'id' => $application->id,
        ], 201);
    }
}
