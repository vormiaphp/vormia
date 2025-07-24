<?php

namespace Vormia\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Vrm\MediaForgeService;

class CheckDependenciesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vormia:check-dependencies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if all required dependencies for Vormia are installed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking Vormia dependencies...');
        $this->newLine();

        $allGood = true;

        // Check intervention/image
        $this->checkInterventionImage($allGood);

        $this->newLine();

        if ($allGood) {
            $this->info('✅ All required dependencies are installed!');
            $this->info('Vormia is ready to use.');
        } else {
            $this->error('❌ Some required dependencies are missing.');
            $this->error('Please install the missing dependencies and try again.');
            return 1;
        }

        return 0;
    }

    /**
     * Check if intervention/image is installed
     */
    private function checkInterventionImage(bool &$allGood): void
    {
        $this->line('Checking intervention/image...');

        if (MediaForgeService::isImageProcessingAvailable()) {
            $this->info('  ✅ intervention/image is installed');
        } else {
            $this->error('  ❌ intervention/image is missing');
            $this->line('     ' . MediaForgeService::getInstallationInstructions());
            $allGood = false;
        }
    }
}
