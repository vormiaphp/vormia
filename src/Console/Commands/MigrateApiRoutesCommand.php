<?php

namespace Vormia\Console\Commands;

use Illuminate\Console\Command;
use VormiaPHP\Vormia\VormiaVormia;

class MigrateApiRoutesCommand extends Command
{
    protected $signature = 'vormia:migrate-api-routes {--dry-run : Preview changes without writing files or clearing caches}';

    protected $description = 'Move Vormia API auth from /api/v1 to /api/vrm and fix production route conflicts';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry run mode — no files or caches will be changed.');
        }

        $vormia = new VormiaVormia();
        $result = $vormia->migrateApiRoutes(['dry_run' => $dryRun]);

        foreach ($result['messages'] as $message) {
            $this->line($message);
        }

        if (! $result['success']) {
            $this->error('API route migration failed.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info($dryRun ? 'Dry run complete.' : 'Vormia API route migration complete.');
        if (! $dryRun) {
            $this->comment('On production, you may run: php artisan route:cache');
        }

        return self::SUCCESS;
    }
}
