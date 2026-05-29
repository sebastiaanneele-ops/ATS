<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\PipelineStage;
use Illuminate\Database\Seeder;

class PipelineStageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            [
                'name' => 'Nieuw',
                'color' => 'gray',
                'position' => 1,
                'is_default' => true,
                'notify_applicant' => true,
                'email_subject' => 'Bevestiging van je sollicitatie – {{vacature}}',
                'email_body' => '<p>Beste {{naam}},</p>'
                    .'<p>Bedankt voor je sollicitatie op de functie <strong>{{vacature}}</strong>. '
                    .'We hebben deze in goede orde ontvangen en bekijken je gegevens zorgvuldig. '
                    .'Je hoort zo snel mogelijk van ons.</p>'
                    .'<p>Met vriendelijke groet,<br>Team Personeel Partners</p>',
            ],
            ['name' => 'Screening', 'color' => 'info', 'position' => 2, 'is_default' => false],
            [
                'name' => 'Gesprek 1',
                'color' => 'warning',
                'position' => 3,
                'is_default' => false,
                'notify_applicant' => true,
                'email_subject' => 'Uitnodiging voor een kennismakingsgesprek – {{vacature}}',
                'email_body' => '<p>Beste {{naam}},</p>'
                    .'<p>Goed nieuws! Naar aanleiding van je sollicitatie op de functie <strong>{{vacature}}</strong> '
                    .'nodigen we je graag uit voor een kennismakingsgesprek. We nemen binnenkort contact met je op '
                    .'om een afspraak in te plannen.</p>'
                    .'<p>Met vriendelijke groet,<br>Team Personeel Partners</p>',
            ],
            ['name' => 'Gesprek 2', 'color' => 'warning', 'position' => 4, 'is_default' => false],
            ['name' => 'Aanbod', 'color' => 'primary', 'position' => 5, 'is_default' => false],
            ['name' => 'Aangenomen', 'color' => 'success', 'position' => 6, 'is_default' => false],
            [
                'name' => 'Afgewezen',
                'color' => 'danger',
                'position' => 7,
                'is_default' => false,
                'notify_applicant' => true,
                'email_subject' => 'Update over je sollicitatie – {{vacature}}',
                'email_body' => '<p>Beste {{naam}},</p>'
                    .'<p>Bedankt voor je interesse in de functie <strong>{{vacature}}</strong> en de tijd die je '
                    .'in je sollicitatie hebt gestoken. Na zorgvuldige afweging hebben we besloten je sollicitatie '
                    .'niet verder in behandeling te nemen.</p>'
                    .'<p>We wensen je veel succes met het vervolg van je loopbaan.</p>'
                    .'<p>Met vriendelijke groet,<br>Team Personeel Partners</p>',
            ],
        ];

        foreach ($stages as $stage) {
            PipelineStage::updateOrCreate(['name' => $stage['name']], $stage);
        }

        // Bestaande sollicitaties zonder fase in de standaardfase plaatsen.
        $default = PipelineStage::default();
        if ($default) {
            Application::whereNull('pipeline_stage_id')->update(['pipeline_stage_id' => $default->id]);
        }
    }
}
