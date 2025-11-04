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

        // Install intervention/image if not already installed
        $this->step('Installing intervention/image package...');
        $this->installInterventionImage();

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

        // Step 7: Update CSS and JS files
        $this->step('Updating CSS and JS files...');
        $this->updateAppCss();
        $this->updateAppJs();

        // Step 8: Install Sanctum (API)
        $this->step('Installing Laravel Sanctum...');
        $this->installSanctum();

        // Step 9: Publish CORS config
        $this->step('Publishing CORS configuration...');
        $this->publishCorsConfig();

        // Step 10: API support information
        $this->step('API support information.');
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
     * Update app.css with Vormia imports
     */
    private function updateAppCss(): void
    {
        $appCssPath = resource_path('css/app.css');

        if (!File::exists($appCssPath)) {
            $this->warn('⚠️  resources/css/app.css not found. Creating it...');
            File::ensureDirectoryExists(resource_path('css'));
            File::put($appCssPath, '');
        }

        $content = File::get($appCssPath);

        // Check if imports already exist
        $importsToAdd = [
            "@import '../../vendor/livewire/flux/dist/flux.css';",
            "@import './plugins/style.min.css';",
        ];

        $needsUpdate = false;
        foreach ($importsToAdd as $import) {
            if (strpos($content, $import) === false) {
                $needsUpdate = true;
                break;
            }
        }

        if ($needsUpdate) {
            // Add imports if not present
            $newContent = trim($content);
            if (!empty($newContent) && substr($newContent, -1) !== "\n") {
                $newContent .= "\n";
            }
            $newContent .= "\n/* Include Style */\n\n";
            foreach ($importsToAdd as $import) {
                if (strpos($content, $import) === false) {
                    $newContent .= $import . "\n";
                }
            }
            File::put($appCssPath, $newContent);
            $this->info('✅ app.css updated successfully.');
        } else {
            $this->info('✅ app.css already contains required imports.');
        }
    }

    /**
     * Update app.js with Vormia imports and initialization
     */
    private function updateAppJs(): void
    {
        $appJsPath = resource_path('js/app.js');

        if (!File::exists($appJsPath)) {
            $this->warn('⚠️  resources/js/app.js not found. Creating it...');
            File::ensureDirectoryExists(resource_path('js'));
            File::put($appJsPath, '');
        }

        $content = File::get($appJsPath);

        // Check if Vormia code already exists
        if (strpos($content, 'loadSelect2') !== false || strpos($content, 'initFlatpickr') !== false) {
            $this->info('✅ app.js already contains Vormia initialization code.');
            return;
        }

        // Add imports and initialization code
        $vormiaCode = "\n\n// Vormia imports and initialization\n";
        $vormiaCode .= "import \"./plugins/jquery\";\n\n";
        $vormiaCode .= "import {\n";
        $vormiaCode .= "    loadSelect2,\n";
        $vormiaCode .= "    initSelect2,\n";
        $vormiaCode .= "    safeReinitializeSelect2,\n";
        $vormiaCode .= "} from \"./plugins/select2\";\n\n";
        $vormiaCode .= "import { initFlatpickr } from \"./plugins/flatpickr\";\n\n";
        $vormiaCode .= "import { setupLivewireHooks } from \"./helpers/livewire-hooks\";\n\n";
        $vormiaCode .= "document.addEventListener(\"DOMContentLoaded\", async () => {\n";
        $vormiaCode .= "    console.log(\"jQuery version:\", $.fn.jquery);\n\n";
        $vormiaCode .= "    const select2Loaded = await loadSelect2();\n";
        $vormiaCode .= "    if (select2Loaded) {\n";
        $vormiaCode .= "        initSelect2();\n";
        $vormiaCode .= "    } else {\n";
        $vormiaCode .= "        console.error(\"CRITICAL: Failed to load Select2\");\n";
        $vormiaCode .= "    }\n\n";
        $vormiaCode .= "    initFlatpickr();\n\n";
        $vormiaCode .= "    if (window.Livewire) {\n";
        $vormiaCode .= "        setupLivewireHooks();\n";
        $vormiaCode .= "    } else {\n";
        $vormiaCode .= "        console.warn(\"Livewire not detected\");\n";
        $vormiaCode .= "    }\n";
        $vormiaCode .= "});\n\n";
        $vormiaCode .= "// Swal\n";
        $vormiaCode .= "import Swal from \"sweetalert2\";\n";
        $vormiaCode .= "// Make it available globally\n";
        $vormiaCode .= "window.Swal = Swal;\n";

        $newContent = trim($content);
        if (!empty($newContent) && substr($newContent, -1) !== "\n") {
            $newContent .= "\n";
        }
        $newContent .= $vormiaCode;

        File::put($appJsPath, $newContent);
        $this->info('✅ app.js updated successfully.');
    }

    /**
     * Install intervention/image package
     */
    private function installInterventionImage(): void
    {
        // Check if already installed
        if (class_exists('Intervention\Image\ImageManager')) {
            $this->info('✅ intervention/image is already installed.');
            return;
        }

        $this->line('   Installing intervention/image...');
        $result = Process::path(base_path())->run('composer require intervention/image');

        if ($result->successful()) {
            $this->info('✅ intervention/image installed successfully.');
        } else {
            $this->warn('⚠️  Failed to install intervention/image automatically.');
            $this->line('   Please install it manually by running: composer require intervention/image');
            if ($result->errorOutput()) {
                $this->line('   Error: ' . $result->errorOutput());
            }
        }
    }

    /**
     * Install Laravel Sanctum
     */
    private function installSanctum(): void
    {
        $this->line('   Running: php artisan install:api');
        $result = Process::path(base_path())->run('php artisan install:api');

        if ($result->successful()) {
            $this->info('✅ Sanctum installed successfully.');
        } else {
            $this->warn('⚠️  Failed to install Sanctum automatically.');
            $this->line('   Please install it manually by running: php artisan install:api');
            if ($result->errorOutput()) {
                $this->line('   Error: ' . $result->errorOutput());
            }
        }
    }

    /**
     * Publish CORS configuration
     */
    private function publishCorsConfig(): void
    {
        $this->line('   Running: php artisan config:publish cors');
        $result = Process::path(base_path())->run('php artisan config:publish cors');

        if ($result->successful()) {
            $this->info('✅ CORS configuration published successfully.');
        } else {
            $this->warn('⚠️  Failed to publish CORS configuration automatically.');
            $this->line('   Please publish it manually by running: php artisan config:publish cors');
            if ($result->errorOutput()) {
                $this->line('   Error: ' . $result->errorOutput());
            }
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
        $this->line('   4. Review and configure Sanctum in your config/sanctum.php');
        $this->line('   5. Review and configure CORS in your config/cors.php');

        $this->newLine();
        $this->comment('📖 For help and available commands, run: php artisan vormia:help');
        $this->newLine();

        $this->info('✨ Happy coding with Vormia!');
    }
}
