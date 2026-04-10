<?php

namespace Vormia\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;
use Vormia\Console\Support\TwoFactorMigrationNormalizer;
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

        // Patch Laravel / Fortify two-factor migrations so they skip columns that already exist
        // (must run before VormiaVormia::install(), which calls migrate).
        $this->step('Normalizing two-factor user migrations (skip if columns exist)...');
        (new TwoFactorMigrationNormalizer)->patchApplicationMigrations(fn (string $msg) => $this->line($msg));

        // Step 2: Install Vormia kit
        $this->step('Installing Vormia kit...');
        if ($vormia->install($isApi)) {
            $this->info('✅ Vormia kit installed successfully.');
        } else {
            $this->error('❌ Failed to install Vormia kit.');
            return 1;
        }

        // Step 3: Update .env files
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

        // Step 9: API support information
        $this->step('API support information.');
        $this->info('A Postman collection has been published to public/Vormia.postman_collection.json.');

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
        $envContent_mediaforge .= "VORMIA_MEDIAFORGE_DISK=public\n";
        $envContent_mediaforge .= "VORMIA_MEDIAFORGE_URL_PASSTHROUGH=false\n";
        $envContent_mediaforge .= "VORMIA_MEDIAFORGE_BASE_DIR=uploads\n";
        $envContent_mediaforge .= "VORMIA_MEDIAFORGE_DEFAULT_QUALITY=85\n";
        $envContent_mediaforge .= "VORMIA_MEDIAFORGE_DEFAULT_FORMAT=webp\n";
        $envContent_mediaforge .= "VORMIA_MEDIAFORGE_AUTO_OVERRIDE=false\n";
        $envContent_mediaforge .= "VORMIA_MEDIAFORGE_PRESERVE_ORIGINALS=true\n";
        $envContent_mediaforge .= "VORMIA_MEDIAFORGE_THUMBNAIL_KEEP_ASPECT_RATIO=true\n";
        $envContent_mediaforge .= "VORMIA_MEDIAFORGE_THUMBNAIL_FROM_ORIGINAL=false\n";

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
     *
     * `php artisan install:api` can publish a migration that adds two-factor columns to
     * `users`. If Fortify, Breeze, or an earlier migration already added those columns,
     * the follow-up migrate fails with "Duplicate column name 'two_factor_secret'".
     * In that case we install Sanctum only (composer + vendor:publish).
     */
    private function installSanctum(): void
    {
        if (class_exists(\Laravel\Sanctum\Sanctum::class)) {
            $this->info('✅ Laravel Sanctum is already installed. Skipping install:api.');

            return;
        }

        if ($this->usersTableAlreadyHasTwoFactorColumns()) {
            $this->warn('⚠️  Your users table already has two-factor columns (e.g. from Fortify or Breeze).');
            $this->line('   Skipping install:api to avoid duplicate migrations; installing Sanctum via composer.');
            $this->installSanctumWithoutApiInstaller();

            return;
        }

        (new TwoFactorMigrationNormalizer)->patchApplicationMigrations(fn (string $msg) => $this->line($msg));

        $this->line('   Running: php artisan install:api --no-interaction');
        $result = Process::path(base_path())->run('php artisan install:api --no-interaction');
        $combinedOutput = trim($result->output() . "\n" . $result->errorOutput());

        if ($result->successful()) {
            $this->info('✅ Sanctum installed successfully.');

            return;
        }

        if ($this->outputLooksLikeDuplicateTwoFactorMigration($combinedOutput)) {
            $this->warn('⚠️  install:api failed due to duplicate two-factor columns on users.');
            $this->line('   Patching two-factor migration(s) to skip existing columns, then re-running migrate.');
            (new TwoFactorMigrationNormalizer)->patchApplicationMigrations(fn (string $msg) => $this->line($msg));
            Artisan::call('migrate', ['--force' => true]);
            $this->info('✅ Migrations re-run after patch.');

            if (! class_exists(\Laravel\Sanctum\Sanctum::class)) {
                $this->line('   Installing Sanctum via composer (install:api did not complete).');
                $this->installSanctumWithoutApiInstaller();
            } else {
                $this->info('✅ Laravel Sanctum is present after migrate.');
            }

            return;
        }

        $this->warn('⚠️  Failed to install Sanctum automatically.');
        $this->line('   Please install manually: php artisan install:api');
        $this->line('   Or: composer require laravel/sanctum && php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider"');
        if ($combinedOutput !== '') {
            $this->line('   Error: ' . $combinedOutput);
        }
    }

    /**
     * True when users.two_factor_secret already exists (2FA migration ran earlier).
     */
    private function usersTableAlreadyHasTwoFactorColumns(): bool
    {
        try {
            return Schema::hasTable('users')
                && Schema::hasColumn('users', 'two_factor_secret');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Detect duplicate column / 2FA migration failure from install:api migrate step.
     */
    private function outputLooksLikeDuplicateTwoFactorMigration(string $output): bool
    {
        $lower = strtolower($output);

        return str_contains($lower, 'duplicate column')
            && str_contains($lower, 'two_factor');
    }

    /**
     * Install Sanctum without `install:api` (no duplicate users-table 2FA migration).
     */
    private function installSanctumWithoutApiInstaller(): void
    {
        $this->line('   Running: composer require laravel/sanctum --no-interaction');
        $require = Process::path(base_path())->run('composer require laravel/sanctum --no-interaction');

        if (! $require->successful()) {
            $this->warn('⚠️  composer require laravel/sanctum failed.');
            $err = trim($require->errorOutput());
            if ($err !== '') {
                $this->line('   ' . $err);
            }
            $this->line('   Install manually: composer require laravel/sanctum');

            return;
        }

        $this->info('✅ laravel/sanctum added.');

        if (! $this->sanctumPersonalAccessTokensMigrationExists()) {
            Artisan::call('vendor:publish', [
                '--provider' => 'Laravel\\Sanctum\\SanctumServiceProvider',
            ]);
            $this->info('✅ Sanctum migration/config published.');
        } else {
            $this->info('✅ personal_access_tokens migration already present; skipping duplicate publish.');
        }

        $this->newLine();
        $this->comment('   If routes/api.php is not registered yet, add it per Laravel Sanctum docs, or run');
        $this->line('   php artisan install:api --no-interaction');
        $this->line('   after removing any duplicate migration that adds two_factor_* columns to users.');
    }

    /**
     * Whether a published Sanctum personal_access_tokens migration already exists.
     */
    private function sanctumPersonalAccessTokensMigrationExists(): bool
    {
        $dir = database_path('migrations');
        if (! is_dir($dir)) {
            return false;
        }

        foreach (glob($dir.'/*.php') ?: [] as $path) {
            if (preg_match('/\d{4}_\d{2}_\d{2}_\d{6}_create_personal_access_tokens_table\.php$/', basename($path))) {
                return true;
            }
        }

        return false;
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
     * Display completion message
     */
    private function displayCompletionMessage()
    {
        $this->newLine();
        $this->info('🎉 Vormia package installed successfully!');
        $this->newLine();

        $this->comment('📋 Next steps (see docs/INSTALLATION.md for details):');
        $this->line('   1. Add the Vormia\Vormia\Traits\HasVormiaRoles trait and is_active to your User model');
        $this->line('      use Vormia\Vormia\Traits\HasVormiaRoles;');
        $this->line('   2. Run: php artisan migrate');
        $this->line('   3. Add HasApiTokens to User for API auth');
        $this->line('   4. In CreateNewUser (or your registration flow): attach default role after creating user');
        $this->newLine();
        $this->comment('   Middleware (role, permission, module, authority, api-auth) and providers are');
        $this->comment('   auto-registered by the package. No manual bootstrap/app.php changes needed.');

        $this->newLine();
        $this->comment('📖 For help see docs/INSTALLATION.md');
        $this->newLine();

        $this->info('✨ Happy coding with Vormia!');
    }
}
