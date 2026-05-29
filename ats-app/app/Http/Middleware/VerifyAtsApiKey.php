<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyAtsApiKey
{
    /**
     * Controleert de gedeelde API-sleutel (header X-ATS-Key).
     * Gebruikt door externe clients zoals de WordPress-plugin (server-side).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.ats.api_key');
        $provided = (string) $request->header('X-ATS-Key', '');

        if ($expected === '' || $provided === '' || ! hash_equals($expected, $provided)) {
            abort(401, 'Ongeldige of ontbrekende API-sleutel.');
        }

        return $next($request);
    }
}
