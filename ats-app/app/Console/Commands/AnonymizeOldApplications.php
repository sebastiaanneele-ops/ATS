<?php

namespace App\Console\Commands;

use App\Models\Application;
use Illuminate\Console\Command;

class AnonymizeOldApplications extends Command
{
    protected $signature = 'ats:anonymize-applications {--dry-run : Toon alleen wat er zou gebeuren, zonder te wijzigen}';

    protected $description = 'Anonimiseer sollicitaties waarvan de bewaartermijn is verstreken (AVG).';

    public function handle(): int
    {
        $days = (int) config('ats.retention_days', 365);
        $dryRun = (bool) $this->option('dry-run');

        $query = Application::query()->dueForRetention($days);
        $count = $query->count();

        if ($count === 0) {
            $this->info("Geen sollicitaties ouder dan {$days} dagen om te anonimiseren.");

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn("[dry-run] {$count} sollicitatie(s) ouder dan {$days} dagen zouden worden geanonimiseerd.");

            return self::SUCCESS;
        }

        $processed = 0;
        $query->chunkById(100, function ($applications) use (&$processed) {
            foreach ($applications as $application) {
                $application->anonymize();
                $processed++;
            }
        });

        $this->info("{$processed} sollicitatie(s) geanonimiseerd (bewaartermijn {$days} dagen).");

        return self::SUCCESS;
    }
}
