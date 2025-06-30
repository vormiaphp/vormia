<?php

namespace Vormia\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use VormiaPHP\Vormia\VormiaVormia;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vormia:install {--api : Install with API support including Sanctum}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Vormia package with all necessary files and configurations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Installing Vormia Package...');

        $isApi = $this->option('api');
        $vormia = new VormiaVormia();

        // Step 1: Publish config
        $this->step('Publishing configuration files...');
        Artisan::call('vendor:publish', [
            '--provider' => 'Vormia\VormiaServiceProvider',
            '--tag' => 'vormia-config',
            '--force' => true
        ]);

        // Step 2: Install Vormia kit
        $this->step('Installing Vormia kit...');
        if ($vormia->install($isApi)) {
            $this->info('✅ Vormia kit installed successfully.');
        } else {
            $this->error('❌ Failed to install Vormia kit.');
            return 1;
        }

        // Step 3: Update User model
        $this->step('Updating User model...');
        $this->updateUserModel();

        // Step 4: Update bootstrap/app.php
        $this->step('Updating bootstrap/app.php...');
        $this->updateBootstrapApp();

        // Step 5: Update .env files
        $this->step('Updating environment files...');
        $this->updateEnvFiles();

        // Step 6: Install API if requested
        if ($isApi) {
            $this->step('Installing API support with Sanctum...');
            $this->installApiSupport();
        }

        $this->displayCompletionMessage($isApi);

        // Final message
        $this->info(PHP_EOL . '🎉 Vormia has been installed successfully!');
        $this->info('Run `php artisan serve` to start your application.');

        return 0;
    }

    /**
     * Display a step message
     */
    private function step($message)
    {
        $this->info("📦 {$message}");
    }

    /**
     * Update the User model with Vormia functionality
     */
    private function updateUserModel()
    {
        $userModelPath = app_path('Models/User.php');
        $stubPath = base_path('vendor/vormiaphp/vormia/src/stubs/models/User.php');
        // If developing inside the package, use local stub path
        if (!file_exists($stubPath)) {
            $stubPath = base_path('src/stubs/models/User.php');
        }

        if (!File::exists($userModelPath)) {
            $this->error('❌ User model not found. Please ensure it exists.');
            return;
        }
        if (!File::exists($stubPath)) {
            $this->error('❌ User.php stub not found in vormia package.');
            return;
        }

        $this->newLine();
        if ($this->confirm('Do you have a backup of your current User.php model?', true) === false) {
            $backupPath = $userModelPath . '.backup.' . date('Y-m-d-H-i-s');
            File::copy($userModelPath, $backupPath);
            $this->info('✅ Backup created: ' . $backupPath);
        }

        File::copy($stubPath, $userModelPath);
        $this->info('✅ User model replaced with Vormia stub.');
    }

    /**
     * Update bootstrap/app.php with middleware and providers
     */
    private function updateBootstrapApp()
    {
        $bootstrapPath = base_path('bootstrap/app.php');

        if (!File::exists($bootstrapPath)) {
            $this->error('❌ bootstrap/app.php not found.');
            return;
        }

        $content = File::get($bootstrapPath);

        // Create backup
        File::copy($bootstrapPath, $bootstrapPath . '.backup.' . date('Y-m-d-H-i-s'));

        // Add middleware aliases
        $middlewareAliases = "
            'role' => \\App\\Http\\Middleware\\Vrm\\CheckRole::class,
            'module' => \\App\\Http\\Middleware\\Vrm\\CheckModule::class,
            'permission' => \\App\\Http\\Middleware\\Vrm\\CheckPermission::class,";

        // Add providers
        $providers = "
        App\\Providers\\Vrm\\NotificationServiceProvider::class,
        App\\Providers\\Vrm\\TokenServiceProvider::class,
        App\\Providers\\Vrm\\MediaForgeServiceProvider::class,
        App\\Providers\\Vrm\\UtilitiesServiceProvider::class,
        App\\Providers\\Vrm\\GlobalDataServiceProvider::class,";

        // Pattern to find middleware aliases section
        if (preg_match('/(->withMiddleware\(function\s*\([^)]*\)\s*\{[^}]*alias\(\s*\[[^]]*)/s', $content, $matches)) {
            $content = str_replace($matches[1], $matches[1] . $middlewareAliases, $content);
        }

        // Pattern to find providers section  
        if (preg_match('/(Application::configure[^}]*providers:\s*\[[^]]*)/s', $content, $matches)) {
            $content = str_replace($matches[1], $matches[1] . $providers, $content);
        }

        File::put($bootstrapPath, $content);
        $this->info('✅ bootstrap/app.php updated successfully (backup created).');

        // --- Manual fallback instructions ---
        $finalContent = File::get($bootstrapPath);
        $missing = [];
        if (strpos($finalContent, "'role' => \\App\\Http\\Middleware\\Vrm\\CheckRole::class") === false) $missing[] = "'role' => \\App\\Http\\Middleware\\Vrm\\CheckRole::class";
        if (strpos($finalContent, "'module' => \\App\\Http\\Middleware\\Vrm\\CheckModule::class") === false) $missing[] = "'module' => \\App\\Http\\Middleware\\Vrm\\CheckModule::class";
        if (strpos($finalContent, "'permission' => \\App\\Http\\Middleware\\Vrm\\CheckPermission::class") === false) $missing[] = "'permission' => \\App\\Http\\Middleware\\Vrm\\CheckPermission::class";
        $providersList = [
            'App\\Providers\\Vrm\\NotificationServiceProvider::class',
            'App\\Providers\\Vrm\\TokenServiceProvider::class',
            'App\\Providers\\Vrm\\MediaForgeServiceProvider::class',
            'App\\Providers\\Vrm\\UtilitiesServiceProvider::class',
            'App\\Providers\\Vrm\\GlobalDataServiceProvider::class',
        ];
        $missingProviders = array_filter($providersList, fn($p) => strpos($finalContent, $p) === false);
        if ($missing || $missingProviders) {
            $this->warn('Some middleware aliases or providers could not be added automatically. Please add them manually to bootstrap/app.php:');
            if ($missing) {
                $this->line("\nAdd these to your middleware aliases array:");
                foreach ($missing as $m) $this->line('    ' . $m);
            }
            if ($missingProviders) {
                $this->line("\nAdd these to your providers array:");
                foreach ($missingProviders as $p) $this->line('    ' . $p . ',');
            }
        }
    }

    /**
     * Update .env and .env.example files
     */
    private function updateEnvFiles()
    {
        $envPath = base_path('.env');
        $envExamplePath = base_path('.env.example');

        $envContent = "\n# VORMIA CONFIG\nVORMIA_TABLE_PREFIX=vrm_\n";

        // Update .env
        if (File::exists($envPath)) {
            $content = File::get($envPath);
            if (strpos($content, 'VORMIA_TABLE_PREFIX') === false) {
                File::append($envPath, $envContent);
            }
        }

        // Update .env.example
        if (File::exists($envExamplePath)) {
            $content = File::get($envExamplePath);
            if (strpos($content, 'VORMIA_TABLE_PREFIX') === false) {
                File::append($envExamplePath, $envContent);
            }
        }

        $this->info('✅ Environment files updated successfully.');
    }

    /**
     * Install API support with Sanctum
     */
    private function installApiSupport()
    {
        // Install Sanctum
        Artisan::call('install:api', ['--without-migration-prompt' => true]);
        $this->info('✅ Laravel API with Sanctum installed successfully.');
    }

    /**
     * Display completion message
     */
    private function displayCompletionMessage($isApi)
    {
        $this->newLine();
        $this->info('🎉 Vormia package installed successfully!');
        $this->newLine();

        $this->comment('📋 Next steps:');
        $this->line('   1. Review your User model and bootstrap/app.php changes');
        $this->line('   2. Configure your .env file with VORMIA_TABLE_PREFIX');
        $this->line('   3. Run: php artisan migrate (if you haven\'t already)');

        if ($isApi) {
            $this->line('   4. Configure Sanctum in your config/sanctum.php');
        }

        $this->newLine();
        $this->comment('📖 For help and available commands, run: php artisan vormia:help');
        $this->newLine();

        $this->info('✨ Happy coding with Vormia!');
    }
}
