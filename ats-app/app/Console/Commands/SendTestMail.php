<?php

namespace App\Console\Commands;

use App\Mail\StageNotificationMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestMail extends Command
{
    protected $signature = 'ats:test-mail {email : Ontvanger van de testmail}';

    protected $description = 'Verstuur een testmail om de SMTP-instellingen te controleren.';

    public function handle(): int
    {
        $email = $this->argument('email');

        $this->info('Versturen via '.config('mail.mailers.smtp.host').':'.config('mail.mailers.smtp.port').' …');

        try {
            Mail::to($email)->send(new StageNotificationMail(
                'Testbericht van het ATS',
                '<p>Hallo,</p><p>Dit is een testbericht om de e-mailinstellingen van het ATS te controleren. '
                .'Ontvang je dit bericht, dan werkt de koppeling met de mailserver.</p>'
                .'<p>Met vriendelijke groet,<br>Personeel Partners</p>'
            ));
        } catch (\Throwable $e) {
            $this->error('Versturen mislukt: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Testmail verzonden naar {$email}.");

        return self::SUCCESS;
    }
}
