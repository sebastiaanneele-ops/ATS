<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// AVG: dagelijks oude sollicitaties anonimiseren wanneer de bewaartermijn verloopt.
Schedule::command('ats:anonymize-applications')->daily();
