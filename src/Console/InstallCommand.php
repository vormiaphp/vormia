<?php

namespace VormiaPHP\Vormia\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use VormiaPHP\Vormia\VormiaVormia;

class InstallCommand extends Command
{
    protected $signature = 'vormia:install';

    protected $description = 'Install Vormia Starter Kit';

    public function handle()
    {
        $this->info('Installing Vormia Starter Kit...');

        // First, check and install dependencies
        $this->checkAndInstallDependencies();

        $starter = new VormiaVormia();
        $starter->install();

        // Add .gitignore entries
        $this->appendToGitIgnore([
            '# Custom Ignore',
            '.DS_Store',
            '/public/media',
            '/storage/app/public/media',
        ]);

        $dotenv = [
            'AFRICASTALKING_USERNAME=',
            'AFRICASTALKING_API_KEY=',
            'AFRICASTALKING_FROM=',
            'OTP_ENCRYPTION_KEY=',
            'OTP_ENCRYPTION_IV='
        ];
        // Append to .env
        $this->appendToEnv($dotenv, '.env');

        // Also add in .env.example
        $this->appendToEnv($dotenv, '.env.example');

        // Append to services.php
        $this->appendToServicesConfig([
            'africastalking' => [
                'api_username' => "env('AFRICASTALKING_USERNAME')",
                'api_key' => "env('AFRICASTALKING_API_KEY')",
                'api_from' => "env('AFRICASTALKING_FROM')",
            ],
            'app_name' => "env('APP_NAME')",
            'app_mail' => [
                'support' => '',
            ],
            'app_url' => [
                'verification' => 'api/v1/auth/register/verify-email',
                'reset' => 'api/v1/auth/password/reset-password',
            ]
        ]);


        $this->info('Vormia Starter Kit files have been installed successfully!');

        // Run API installation
        // $this->call('install:api');

        // Run API installation only if not already installed
        if (!$this->isApiInstalled()) {
            $this->call('install:api');
        } else {
            $this->info('API is already installed. Skipping installation.');
        }

        // Adding Web Route
        if (!$this->option('no-interaction') && !$this->confirm('Would you like to add vormia web route?', true)) {
            $this->info('Web route skipped.');
            return;
        }

        $this->info('Adding web routes...');

        // Adding Web Route
        $this->addWebRoutesToExistingFile();

        // Adding Api Route
        if (!$this->option('no-interaction') && !$this->confirm('Would you like to add vormia api route?', true)) {
            $this->info('Api route skipped.');
            return;
        }

        $this->info('Adding api routes...');

        // Adding Api Route
        $this->addApiRoutesToExistingFile();

        // Check if we should run database commands
        if (!$this->option('no-interaction') && !$this->confirm('Would you like to set up the database now? Backup your database.', true)) {
            $this->info('Database setup skipped.');
            return;
        }

        $this->info('Setting up the database...');

        // Run database commands
        $this->call('migrate');
        $this->call('db:seed');

        $this->info('Vormia Starter Kit has been completely installed!');
        $this->warn('To use Vormia Email/Sms remember to queue the jobs. `` php artisan queue:work ``');
        $this->warn('To use Vormia Livewire remember to run `` npm install && npm run dev ``');
        $this->info('Remember to run "php artisan serve" to start your application.');
    }

    /**
     * Add routes to the existing web.php file
     */
    protected function addWebRoutesToExistingFile()
    {
        $webRoutesPath = base_path('routes/web.php');

        if (!file_exists($webRoutesPath)) {
            $this->error('Routes file not found: ' . $webRoutesPath);
            return false;
        }

        $content = file_get_contents($webRoutesPath);

        // Check if routes are already added
        if (str_contains($content, '// Vormia Routes')) {
            $this->info('Vormia routes already exist in web.php');
            return true;
        }

        // Add the import statement after the Route import
        $importPattern = '/use Illuminate\\Support\\Facades\\Route;/';
        $importReplacement = "use Illuminate\\Support\\Facades\\Route;\nuse App\\Http\\Middleware\\CheckRolePermission;";

        // Check if the middleware import already exists
        if (!str_contains($content, 'use App\Http\Middleware\CheckRolePermission;')) {
            // Add the import statement after the Route import
            $importPattern = '/use Illuminate\\\\Support\\\\Facades\\\\Route;/';
            $importReplacement = "use Illuminate\\Support\\Facades\\Route;\nuse App\\Http\\Middleware\\CheckRolePermission;";

            // Replace the import line
            $content = preg_replace($importPattern, $importReplacement, $content);
        }

        // Add your routes at the end of the file
        $vormiaRoutes = <<<'EOT'
        
        // TODO: VORMIA ROUTES
        Route::group(['prefix' => 'vrm'], function () {
        // todo: login - admin
        Route::controller(App\Http\Controllers\Admin\LoginController::class)->group(function () {
            Route::get('/admin', 'index')->name('/vrm/admin');
            Route::post('/admin/access', 'login');
            Route::get('/admin/logout', 'logout')->name('/vrm/admin/logout');
        });

        Route::middleware([CheckRolePermission::class . ':permissions'])->group(function () {

            Route::middleware([CheckRolePermission::class . ':users'])->group(function () {
                // ? Users
                Route::controller(App\Http\Controllers\Admin\UserController::class)->group(function () {
                    Route::get('/users', 'index');
                    Route::post('/users/save', 'store');
                    Route::post('/users/update', 'update');
                    Route::get('/users/edit/{page?}', 'edit'); // Edit
                    Route::get('/users/delete', 'delete'); // Delete
                    Route::get('/users/status/{action?}', 'valid'); // Valid
                    Route::get('/users/{view}', 'open'); // Open
                });
            });

            // ? Roles
            Route::controller(App\Http\Controllers\Admin\RoleController::class)->group(function () {
                Route::get('/roles', 'index');
                Route::post('/roles/save', 'store');
                Route::post('/roles/update', 'update');
                Route::get('/roles/edit/{page?}', 'edit');
                Route::get('/roles/delete', 'delete');
                Route::get('/roles/{action}', 'valid');
            });
        });

        // Protect a group of routes
        Route::middleware([CheckRolePermission::class . ':dashboard'])->group(function () {
            // ? Dashboard
            Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('vrm/dashboard')->middleware(CheckRolePermission::class . ':dashboard');;
        });

        // Protect a group of routes
        Route::middleware([CheckRolePermission::class . ':setup'])->group(function () {
            // ? Setup
            Route::group(['prefix' => 'setup'], function () {
                // ? Continent Hierarchies
                Route::controller(App\Http\Controllers\Setup\ContinentController::class)->group(function () {
                    Route::get('/continent', 'index');
                    Route::post('/continent/save', 'store');
                    Route::post('/continent/update', 'update');
                    Route::get('/continent/edit/{page?}', 'edit');
                    Route::get('/continent/delete', 'delete');
                    Route::get('/continent/{action}', 'valid');
                });

                // ? Currency
                Route::controller(App\Http\Controllers\Setup\CurrencyController::class)->group(function () {
                    Route::get('/currency', 'index');
                    Route::post('/currency/save', 'store');
                    Route::post('/currency/update', 'update');
                    Route::get('/currency/edit/{page?}', 'edit'); // Edit
                    Route::get('/currency/delete', 'delete'); // Delete
                    Route::get('/currency/status/{action?}', 'valid'); // Valid
                    Route::get('/currency/{view}', 'open'); // Open
                });
            });
        });
        });

        // TODO: VORMIA LIVEWIRE
        Route::get('/', App\Livewire\LiveSetting::class)->name('home');
        EOT;

        file_put_contents($webRoutesPath, $content . $vormiaRoutes);
        $this->info('Added Vormia routes to web.php');

        return true;
    }

    /**
     * Add routes to the existing api.php file
     */
    protected function addApiRoutesToExistingFile()
    {
        $apiRoutesPath = base_path('routes/api.php');

        if (!file_exists($apiRoutesPath)) {
            $this->error('Routes file not found: ' . $apiRoutesPath);
            return false;
        }

        $content = file_get_contents($apiRoutesPath);

        // Check if routes are already added
        if (str_contains($content, '// Vormia Routes')) {
            $this->info('Vormia routes already exist in api.php');
            return true;
        }

        // Add your routes at the end of the file
        $vormiaRoutes = <<<'EOT'

        // Todo: API VERSION 1
        Route::group(['prefix' => 'v1'], function () {
            // Todo: v1 Auth
            Route::group(['prefix' => '/auth'], function () {
                Route::group(['prefix' => '/register'], function () {
                    Route::controller(App\Http\Controllers\Api\V1\Auth\Registration::class)->group(function () {
                        Route::post('/user', 'user_registration');

                        // verification
                        Route::get('/verify-email', 'verification_email');
                        Route::get('/verify-phone', 'verification_phone');
                    });
                });

                Route::group(['prefix' => '/password'], function () {
                    Route::controller(App\Http\Controllers\Api\V1\Auth\Password::class)->group(function () {
                        Route::post('/reset-email', 'reset_via_email');
                        Route::post('/reset-phone', 'reset_via_phone');
                        Route::post('/set-password', 'set_new_password');
                    });
                });
            });
        });

        EOT;

        file_put_contents($apiRoutesPath, $content . $vormiaRoutes);
        $this->info('Added Vormia routes to api.php');

        return true;
    }

    /**
     * Check and install required dependencies.
     */
    protected function checkAndInstallDependencies()
    {
        $dependencies = [
            'intervention/image' => \Intervention\Image\ImageManager::class,
            'laravel/sanctum' => \Laravel\Sanctum\HasApiTokens::class,
            'livewire/livewire' => \Livewire\Livewire::class,
            'africastalking/africastalking' => \AfricasTalking\SDK\AfricasTalking::class,
        ];

        $missingDependencies = [];

        foreach ($dependencies as $package => $class) {
            if (!class_exists($class)) {
                $missingDependencies[] = $package;
            }
        }

        if (empty($missingDependencies)) {
            $this->info('All required dependencies are already installed.');
            return;
        }

        $this->info('Installing required dependencies...');

        foreach ($missingDependencies as $package) {
            $this->info("Installing {$package}...");

            $result = Process::run('composer require ' . $package);

            if ($result->successful()) {
                $this->info("{$package} installed successfully.");
            } else {
                $this->error("Failed to install {$package}.");
                $this->error($result->errorOutput());
                $this->warn("Please run 'composer require {$package}' manually.");
            }
        }
    }

    /**
     * Append lines to .gitignore.
     */
    protected function appendToGitIgnore(array $lines)
    {
        $gitignorePath = base_path('.gitignore');
        $content = file_exists($gitignorePath) ? file_get_contents($gitignorePath) : '';

        // Add each line if it doesn't already exist
        foreach ($lines as $line) {
            if (!str_contains($content, $line)) {
                $content .= PHP_EOL . $line;
            }
        }

        file_put_contents($gitignorePath, $content);
    }

    /**
     * Append lines to .env file.
     */
    protected function appendToEnv(array $lines, $file = '.env')
    {
        $envPath = base_path($file);
        $content = file_exists($envPath) ? file_get_contents($envPath) : '';

        // Add each line if it doesn't already exist
        foreach ($lines as $line) {
            // Extract the key (before the first '=')
            $key = strtok($line, '=');

            // Check if the key already exists in the content
            if (!str_contains($content, $key)) {
                $content .= PHP_EOL . $line;
            }
        }

        file_put_contents($envPath, trim($content) . PHP_EOL);
    }

    /**
     * Append configuration to config/services.php.
     */
    protected function appendToServicesConfig(array $config)
    {
        $servicesPath = config_path('services.php');

        // Read the current content of the services.php file
        $content = file_get_contents($servicesPath);

        // Remove the closing bracket and semicolon, and trim whitespace
        $content = preg_replace('/\s*\];?\s*$/', '', $content);

        // Ensure we end with a newline
        $content = rtrim($content) . PHP_EOL;

        // Add new configuration
        foreach ($config as $key => $value) {
            // If the value is an array, use the Laravel config array syntax
            if (is_array($value)) {
                $content .= "    '{$key}' => [\n";
                foreach ($value as $subKey => $subValue) {
                    // Check if the value is a string starting with 'env('
                    if (is_string($subValue) && strpos($subValue, 'env(') === 0) {
                        $content .= "        '{$subKey}' => {$subValue},\n";
                    } else {
                        $content .= "        '{$subKey}' => " . var_export($subValue, true) . ",\n";
                    }
                }
                $content .= "    ],\n";
            } else {
                // Check if the value is a string starting with 'env('
                if (is_string($value) && strpos($value, 'env(') === 0) {
                    $content .= "    '{$key}' => {$value},\n";
                } else {
                    // For non-array values
                    $content .= "    '{$key}' => " . var_export($value, true) . ",\n";
                }
            }
        }

        // Add back the closing bracket
        $content .= "];";

        // Write the updated content back to the file
        file_put_contents($servicesPath, $content);
    }

    // Add a method to check if API is already installed
    protected function isApiInstalled()
    {
        // You can implement various checks here, such as:
        // 1. Check for specific API-related files
        // 2. Check database tables
        // 3. Check configuration settings

        // Example implementation checking for a specific file or configuration
        $apiConfigPath = config_path('api.php');
        return file_exists($apiConfigPath);
    }
}
