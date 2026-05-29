<?php

use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\VacancyController;
use App\Http\Middleware\VerifyAtsApiKey;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Publieke leesendpoints (alleen gepubliceerde vacatures).
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('vacancies', [VacancyController::class, 'index']);
        Route::get('vacancies/{slug}', [VacancyController::class, 'show']);
    });

    // Sollicitatie indienen: vereist gedeelde API-sleutel + striktere rate limit.
    Route::post('vacancies/{slug}/applications', [ApplicationController::class, 'store'])
        ->middleware([VerifyAtsApiKey::class, 'throttle:10,1']);
});
