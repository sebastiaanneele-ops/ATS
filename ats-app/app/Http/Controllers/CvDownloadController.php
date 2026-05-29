<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CvDownloadController extends Controller
{
    /**
     * Download het cv van een sollicitant. Alleen voor ingelogde panelgebruikers.
     * Het bestand staat op de private disk en is niet publiek benaderbaar.
     */
    public function __invoke(Application $application): StreamedResponse
    {
        abort_unless(
            $application->hasCv() && Storage::disk('local')->exists($application->cv_path),
            404
        );

        return Storage::disk('local')->download(
            $application->cv_path,
            $application->cv_original_name ?: 'cv.pdf'
        );
    }
}
