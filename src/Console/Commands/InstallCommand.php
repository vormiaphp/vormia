<?php

namespace Vormia\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

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

        // Step 1: Publish config
        $this->step('Publishing configuration files...');
        Artisan::call('vendor:publish', [
            '--provider' => 'Vormia\VormiaServiceProvider',
            '--tag' => 'vormia-config',
            '--force' => true
        ]);

        // Step 2: Publish all package files
        $this->step('Publishing package files...');
        Artisan::call('vendor:publish', [
            '--provider' => 'Vormia\VormiaServiceProvider',
            '--tag' => 'vormia-files',
            '--force' => true
        ]);

        // Step 3: Publish migrations
        $this->step('Publishing migrations...');
        Artisan::call('vendor:publish', [
            '--provider' => 'Vormia\VormiaServiceProvider',
            '--tag' => 'vormia-migrations',
            '--force' => true
        ]);

        // Step 4: Update User model
        $this->step('Updating User model...');
        $this->updateUserModel();

        // Step 5: Update bootstrap/app.php
        $this->step('Updating bootstrap/app.php...');
        $this->updateBootstrapApp();

        // Step 6: Update .env files
        $this->step('Updating environment files...');
        $this->updateEnvFiles();

        // Step 7: Install API if requested
        if ($isApi) {
            $this->step('Installing API support with Sanctum...');
            $this->installApiSupport();
        }

        // Step 8: Run migrations
        if ($this->confirm('Do you want to run migrations now?', true)) {
            $this->step('Running migrations...');
            Artisan::call('migrate');
            $this->info('✅ Migrations completed successfully.');
        }

        $this->displayCompletionMessage($isApi);
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

        if (!File::exists($userModelPath)) {
            $this->error('❌ User model not found. Please ensure it exists.');
            return;
        }

        $userModelContent = File::get($userModelPath);

        // Check if already updated
        if (strpos($userModelContent, 'User meta') !== false) {
            $this->warn('⚠️  User model appears to already be updated.');
            return;
        }

        // Create backup
        File::copy($userModelPath, $userModelPath . '.backup.' . date('Y-m-d-H-i-s'));

        // Update fillable
        $fillablePattern = '/protected\s+\$fillable\s*=\s*\[(.*?)\];/s';
        $newFillable = "protected \$fillable = [
        'name',
        'email',
        'password',
        'username',
        'phone',
        'is_active',
    ];";

        $userModelContent = preg_replace($fillablePattern, $newFillable, $userModelContent);

        // Update casts
        $castsPattern = '/protected\s+function\s+casts\(\)\s*:\s*array\s*\{(.*?)\}/s';
        $newCasts = "protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }";

        if (preg_match($castsPattern, $userModelContent)) {
            $userModelContent = preg_replace($castsPattern, $newCasts, $userModelContent);
        } else {
            // Add casts method if it doesn't exist
            $userModelContent = str_replace(
                $newFillable,
                $newFillable . "\n\n    " . $newCasts,
                $userModelContent
            );
        }

        // Add Vormia methods
        $vormiaMethodsPath = __DIR__ . '/../../stubs/user-methods.stub';
        if (File::exists($vormiaMethodsPath)) {
            $vormiaMethods = File::get($vormiaMethodsPath);

            // Add before the closing class brace
            $userModelContent = preg_replace('/(\n\s*})(\s*)$/', "\n" . $vormiaMethods . "$1$2", $userModelContent);
        }

        File::put($userModelPath, $userModelContent);
        $this->info('✅ User model updated successfully (backup created).');
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
