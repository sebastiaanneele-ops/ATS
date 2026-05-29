<?php

use App\Http\Controllers\CvDownloadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Beveiligde CV-download (alleen ingelogde gebruikers / panelbeheerders).
Route::get('admin/applications/{application}/cv', CvDownloadController::class)
    ->middleware('auth')
    ->name('admin.applications.cv');
