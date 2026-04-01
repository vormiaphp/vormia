<?php

namespace Vormia\Console\Commands;

use Illuminate\Console\Command;
use VormiaPHP\Vormia\VormiaVormia;

class UpdateCommand extends Command
{
    protected $signature = 'vormia:update {--force : Skip confirmation prompts}';

    protected $description = 'Update Vormia package files (removes old files and copies fresh ones)';

    public function handle(): int
    {
        if (! $this->option('force')) {
            if (! $this->confirm('This will refresh Vormia files in your application. Continue?', false)) {
                $this->warn('Update aborted.');
                return self::SUCCESS;
            }
        }

        $this->publishFreshFiles();

        $this->info('✅ Vormia update completed.');
        return self::SUCCESS;
    }

    public function publishFreshFiles(): void
    {
        $vormia = new VormiaVormia();
        $vormia->uninstall();
        $vormia->install(true);
    }
}

