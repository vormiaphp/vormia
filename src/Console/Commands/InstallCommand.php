<?php

namespace Vormia\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use VormiaPHP\Vormia\VormiaVormia;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vormia:install';

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

        // Check for required dependencies
        $this->checkRequiredDependencies();

        $isApi = true; // API support is always included
        $vormia = new VormiaVormia();

        // Step 1: Publish config
        $this->step('Publishing configuration files...');
        Artisan::call('vendor:publish', [
            '--provider' => 'VormiaPHP\Vormia\VormiaServiceProvider',
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

        // Step 6: Install npm packages
        $this->step('Installing npm packages...');
        $this->installNpmPackages();

        // Step 7: API support information
        $this->step('API support included. Sanctum is required.');
        $this->info('Please run: php artisan install:api');
        $this->info('This will install Laravel Sanctum and set up API authentication.');
        $this->info('A Postman collection has been published to public/Vormia.postman_collection.json. Download it to test your API endpoints.');
        $this->warn('Reminder: Add the HasApiTokens trait to your User model (app/Models/User.php) for API authentication.');

        $this->displayCompletionMessage();

        // Final message
        $this->info(PHP_EOL . '🎉 Vormia has been installed successfully!');
        $this->info('Run `php artisan serve` to start your application.');

        return 0;
    }

    /**
     * Check for required dependencies
     */
    private function checkRequiredDependencies(): void
    {
        $this->step('Checking required dependencies...');

        // Check for intervention/image
        if (!class_exists('Intervention\Image\ImageManager')) {
            $this->warn('⚠️  The intervention/image package is required for MediaForge functionality.');
            $this->line('   Please install it by running: composer require intervention/image');
            $this->line('   This package is needed for image processing features like resizing, compression, and watermarking.');
            $this->newLine();
        } else {
            $this->info('✅ intervention/image package is installed.');
        }
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

        /*
        $this->newLine();
        if ($this->confirm('Would you like to create a backup of your current User.php model?', true)) {
            $backupPath = $userModelPath . '.backup.' . date('Y-m-d-H-i-s');
            File::copy($userModelPath, $backupPath);
            $this->info('✅ Backup created: ' . $backupPath);
        }
        */

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

        // Add middleware aliases
        $middlewareAliases = "
            'role' => \\App\\Http\\Middleware\\Vrm\\CheckRole::class,
            'module' => \\App\\Http\\Middleware\\Vrm\\CheckModule::class,
            'permission' => \\App\\Http\\Middleware\\Vrm\\CheckPermission::class,
            'api-auth' => \\App\\Http\\Middleware\\Vrm\\ApiAuthenticate::class,";

        // Add providers
        $providers = "
        App\\Providers\\Vrm\\NotificationServiceProvider::class,
        App\\Providers\\Vrm\\TokenServiceProvider::class,
        App\\Providers\\Vrm\\MediaForgeServiceProvider::class,
        App\\Providers\\Vrm\\UtilitiesServiceProvider::class,
        App\\Providers\\Vrm\\GlobalDataServiceProvider::class,";

        // Pattern to find middleware aliases section
        if (preg_match('/(->withMiddleware\\(function\\s*\\([^)]*\\)\\s*\\{[^}]*alias\\(\\s*\\[[^]]*)/s', $content, $matches)) {
            $content = str_replace($matches[1], $matches[1] . $middlewareAliases, $content);
        }

        // Pattern to find providers section  
        if (preg_match('/(Application::configure[^}]*providers:\\s*\\[[^]]*)/s', $content, $matches)) {
            $content = str_replace($matches[1], $matches[1] . $providers, $content);
        }

        File::put($bootstrapPath, $content);
        $this->info('✅ bootstrap/app.php updated successfully.');

        // --- Manual fallback instructions ---
        $finalContent = File::get($bootstrapPath);
        $missing = [];
        if (strpos($finalContent, "'role' => \\App\\Http\\Middleware\\Vrm\\CheckRole::class") === false) $missing[] = "'role' => \\App\\Http\\Middleware\\Vrm\\CheckRole::class";
        if (strpos($finalContent, "'module' => \\App\\Http\\Middleware\\Vrm\\CheckModule::class") === false) $missing[] = "'module' => \\App\\Http\\Middleware\\Vrm\\CheckModule::class";
        if (strpos($finalContent, "'permission' => \\App\\Http\\Middleware\\Vrm\\CheckPermission::class") === false) $missing[] = "'permission' => \\App\\Http\\Middleware\\Vrm\\CheckPermission::class";
        if (strpos($finalContent, "'api-auth' => \\App\\Http\\Middleware\\Vrm\\ApiAuthenticate::class") === false) $missing[] = "'api-auth' => \\App\\Http\\Middleware\\Vrm\\ApiAuthenticate::class";
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

        $envContent_table = "\n# VORMIA CONFIG\nVORMIA_TABLE_PREFIX=vrm_\n";

        $envContent_slug = "\n# VORMIA SLUG CONFIG\nVORMIA_AUTO_UPDATE_SLUGS=false\n";
        $envContent_slug .= "VORMIA_SLUG_APPROVAL_REQUIRED=true\n";
        $envContent_slug .= "VORMIA_SLUG_HISTORY_ENABLED=true\n";

        // MediaForge config
        $envContent_mediaforge = "\n# VORMIA MEDIAFORGE CONFIG\nVORMIA_MEDIAFORGE_DRIVER=auto\n";
        $envContent_mediaforge .= "VORMIA_MEDIAFORGE_DEFAULT_QUALITY=85\n";
        $envContent_mediaforge .= "VORMIA_MEDIAFORGE_DEFAULT_FORMAT=webp\n";
        $envContent_mediaforge .= "VORMIA_MEDIAFORGE_AUTO_OVERRIDE=false\n";
        $envContent_mediaforge .= "VORMIA_MEDIAFORGE_PRESERVE_ORIGINALS=true\n";

        // Update .env
        if (File::exists($envPath)) {
            $content = File::get($envPath);
            if (strpos($content, 'VORMIA_TABLE_PREFIX') === false) {
                File::append($envPath, $envContent_table);
            }
            if (strpos($content, 'VORMIA_AUTO_UPDATE_SLUGS') === false) {
                File::append($envPath, $envContent_slug);
            }
            if (strpos($content, 'VORMIA_MEDIAFORGE_DRIVER') === false) {
                File::append($envPath, $envContent_mediaforge);
            }
        }

        // Update .env.example
        if (File::exists($envExamplePath)) {
            $content = File::get($envExamplePath);
            if (strpos($content, 'VORMIA_TABLE_PREFIX') === false) {
                File::append($envExamplePath, $envContent_table);
            }
            if (strpos($content, 'VORMIA_AUTO_UPDATE_SLUGS') === false) {
                File::append($envExamplePath, $envContent_slug);
            }
            if (strpos($content, 'VORMIA_MEDIAFORGE_DRIVER') === false) {
                File::append($envExamplePath, $envContent_mediaforge);
            }
        }

        $this->info('✅ Environment files updated successfully.');
    }

    /**
     * Install npm packages required by Vormia
     */
    private function installNpmPackages(): void
    {
        $packageJsonPath = base_path('package.json');

        // Check if package.json exists
        if (!File::exists($packageJsonPath)) {
            $this->warn('⚠️  package.json not found. Skipping npm package installation.');
            $this->line('   Please ensure you have a package.json file in your project root.');
            $this->line('   You can manually install the required packages:');
            $this->line('     npm i jquery flatpickr --save select2 sweetalert2');
            return;
        }

        // Check if npm is available
        $npmCheck = Process::run('npm --version');
        if (!$npmCheck->successful()) {
            $this->warn('⚠️  npm is not available. Skipping npm package installation.');
            $this->line('   Please install npm and node.js, then run:');
            $this->line('     npm i jquery flatpickr --save select2 sweetalert2');
            return;
        }

        $this->info('✅ npm is available.');

        // Packages to install with their specific flags
        $packages = [
            ['name' => 'jquery', 'flags' => ''],
            ['name' => 'flatpickr', 'flags' => '--save'],
            ['name' => 'select2', 'flags' => ''],
            ['name' => 'sweetalert2', 'flags' => ''],
        ];

        $failed = [];

        foreach ($packages as $package) {
            $packageName = $package['name'];
            $flags = $package['flags'];
            $command = trim("npm install {$packageName} {$flags}");
            
            $this->line("   Installing {$packageName}...");
            $result = Process::path(base_path())->run($command);

            if ($result->successful()) {
                $this->info("   ✅ {$packageName} installed successfully.");
            } else {
                $this->warn("   ⚠️  Failed to install {$packageName}.");
                $failed[] = $packageName;
                if ($result->errorOutput()) {
                    $this->line("   Error: " . $result->errorOutput());
                }
            }
        }

        if (empty($failed)) {
            $this->info('✅ All npm packages installed successfully.');
        } else {
            $this->warn('⚠️  Some npm packages failed to install: ' . implode(', ', $failed));
            $this->line('   You can manually install them later with:');
            $this->line('     npm i jquery flatpickr --save select2 sweetalert2');
        }
    }

    /**
     * Install API support with Sanctum
     */
    private function installApiSupport()
    {
        // Deprecated: User must now install Sanctum manually.
        $this->warn('Automatic Sanctum installation is no longer supported. Please run: php artisan install:api');
    }

    /**
     * Display completion message
     */
    private function displayCompletionMessage()
    {
        $this->newLine();
        $this->info('🎉 Vormia package installed successfully!');
        $this->newLine();

        $this->comment('📋 Next steps:');
        $this->line('   1. Review your app/Models/User.php model, bootstrap/app.php and bootstrap/providers.php changes');
        $this->line('   2. Configure your .env file with VORMIA');
        $this->line('   3. Run: php artisan migrate (if you haven\'t already)');
        $this->line('   4. Install Sanctum by running: php artisan install:api');
        $this->line('   5. Configure Sanctum in your config/sanctum.php');

        $this->newLine();
        $this->comment('📖 For help and available commands, run: php artisan vormia:help');
        $this->newLine();

        $this->info('✨ Happy coding with Vormia!');
    }
}
