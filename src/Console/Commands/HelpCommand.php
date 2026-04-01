<?php

namespace Vormia\Console\Commands;

use Illuminate\Console\Command;

class HelpCommand extends Command
{
    protected $signature = 'vormia:help';

    protected $description = 'Show available Vormia commands and basic usage';

    public function handle(): int
    {
        $this->info('Vormia commands');
        $this->newLine();

        $this->line('  - vormia:install');
        $this->line('  - vormia:fix-two-factor-migrations');
        $this->line('  - vormia:update');
        $this->line('  - vormia:uninstall');

        $this->newLine();
        $this->comment('Tip: use --help on any command for options.');

        return self::SUCCESS;
    }
}

