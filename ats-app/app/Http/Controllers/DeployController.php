<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;

class DeployController extends Controller
{
    /**
     * Deploy-hook: draait na een code-update de migraties en herbouwt de caches,
     * via de webserver (PHP 8.4) — handig omdat de CLI in de chroot een oudere
     * PHP-versie heeft. Beveiligd met ATS_DEPLOY_TOKEN.
     */
    public function __invoke(Request $request): Response
    {
        $expected = (string) config('ats.deploy_token');
        $provided = (string) $request->query('token', '');

        abort_if($expected === '' || ! hash_equals($expected, $provided), 403, 'Forbidden');

        $steps = [
            'migrate' => fn () => Artisan::call('migrate', ['--force' => true]),
            'config:cache' => fn () => Artisan::call('config:cache'),
            'route:cache' => fn () => Artisan::call('route:cache'),
            'view:clear' => fn () => Artisan::call('view:clear'),
        ];

        $log = [];

        foreach ($steps as $name => $step) {
            try {
                $step();
                $output = trim(Artisan::output());
                $log[] = "[$name] OK".($output !== '' ? "\n".$output : '');
            } catch (\Throwable $e) {
                $log[] = "[$name] FOUT: ".$e->getMessage();
            }
        }

        return response("ATS deploy afgerond:\n\n".implode("\n\n", $log)."\n", 200)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
