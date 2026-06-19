<?php

use App\Http\Controllers\CvDownloadController;
use App\Http\Controllers\DeployController;
use Illuminate\Support\Facades\Route;

// Kale homepage doorsturen naar het beheerpaneel (en cacheable: geen closure).
Route::redirect('/', '/admin');

// Beveiligde CV-download (alleen ingelogde gebruikers / panelbeheerders).
Route::get('admin/applications/{application}/cv', CvDownloadController::class)
    ->middleware('auth')
    ->name('admin.applications.cv');

// Deploy-hook: draait migraties + herbouwt caches via de webserver (PHP 8.4).
// Beveiligd met ATS_DEPLOY_TOKEN. Aan te roepen na een Git-pull op de server.
Route::get('__ops/deploy', DeployController::class)->name('ops.deploy');
