<?php

namespace Vormia\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Vormia\Console\Support\TwoFactorMigrationNormalizer;

class FixTwoFactorMigrationsCommand extends Command
{
    protected $signature = 'vormia:fix-two-factor-migrations {--migrate : Run php artisan migrate --force after patching}';

    protected $description = 'Rewrite two-factor user migrations to skip columns that already exist (Fortify / install:api safe)';

    public function handle(): int
    {
        $this->info('Checking database/migrations for two-factor user migrations...');

        $patched = (new TwoFactorMigrationNormalizer)->patchApplicationMigrations(fn (string $msg) => $this->line($msg));

        if ($this->option('migrate')) {
            $this->newLine();
            $this->info('Running migrations...');
            Artisan::call('migrate', ['--force' => true]);
            $this->output->write(Artisan::output());
        } elseif ($patched > 0) {
            $this->newLine();
            $this->comment('Run: php artisan migrate');
        }

        return self::SUCCESS;
    }
}
