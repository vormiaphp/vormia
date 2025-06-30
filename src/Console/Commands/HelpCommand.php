<?php

namespace Vormia\Console\Commands;

use Illuminate\Console\Command;

class HelpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vormia:help';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display help information for Vormia package commands';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->displayHeader();
        $this->displayCommands();
        $this->displayUsageExamples();
        $this->displayFooter();
    }

    /**
     * Display the header
     */
    private function displayHeader()
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║                         VORMIA HELP                         ║');
        $this->info('║                      Version 4.0.0                         ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->comment('🚀 Vormia is a comprehensive Laravel package for user management,');
        $this->comment('   roles, permissions, and utilities.');
        $this->newLine();
    }

    /**
     * Display available commands
     */
    private function displayCommands()
    {
        $this->info('📋 AVAILABLE COMMANDS:');
        $this->newLine();

        $commands = [
            [
                'command' => 'vormia:install',
                'description' => 'Install Vormia package with all files and configurations',
                'options' => '--api (Install with API support including Sanctum)'
            ],
            [
                'command' => 'vormia:help',
                'description' => 'Display this help information',
                'options' => null
            ],
            [
                'command' => 'vormia:update',
                'description' => 'Update package files (removes old files and copies fresh ones)',
                'options' => '--force (Skip confirmation prompts)'
            ],
            [
                'command' => 'vormia:uninstall',
                'description' => 'Remove all Vormia package files and configurations',
                'options' => '--force (Skip confirmation prompts)'
            ]
        ];

        foreach ($commands as $cmd) {
            $this->line("  <fg=green>{$cmd['command']}</>");
            $this->line("    {$cmd['description']}");
            if ($cmd['options']) {
                $this->line("    <fg=yellow>Options:</> {$cmd['options']}");
            }
            $this->newLine();
        }
    }

    /**
     * Display usage examples
     */
    private function displayUsageExamples()
    {
        $this->info('💡 USAGE EXAMPLES:');
        $this->newLine();

        $examples = [
            [
                'title' => 'Basic Installation',
                'command' => 'php artisan vormia:install',
                'description' => 'Install Vormia with standard configuration'
            ],
            [
                'title' => 'API Installation',
                'command' => 'php artisan vormia:install --api',
                'description' => 'Install Vormia with API support and Sanctum'
            ],
            [
                'title' => 'Update Package',
                'command' => 'php artisan vormia:update',
                'description' => 'Update all package files to latest version'
            ],
            [
                'title' => 'Force Update',
                'command' => 'php artisan vormia:update --force',
                'description' => 'Update without confirmation prompts'
            ],
            [
                'title' => 'Uninstall Package',
                'command' => 'php artisan vormia:uninstall',
                'description' => 'Remove all Vormia files and configurations'
            ]
        ];

        foreach ($examples as $example) {
            $this->line("  <fg=cyan>{$example['title']}:</>");
            $this->line("    <fg=white>{$example['command']}</>");
            $this->line("    <fg=gray>{$example['description']}</>");
            $this->newLine();
        }
    }

    /**
     * Display package features
     */
    private function displayFeatures()
    {
        $this->info('✨ PACKAGE FEATURES:');
        $this->newLine();

        $features = [
            'User Management' => 'Extended user model with meta data support',
            'Role-Based Access' => 'Comprehensive role and permission system',
            'Middleware' => 'Built-in middleware for role, module, and permission checks',
            'Utilities' => 'Helper functions and services for common tasks',
            'Media Management' => 'Image processing with Intervention Image',
            'Notifications' => 'Advanced notification system',
            'API Support' => 'Optional API installation with Sanctum',
            'Migrations' => 'Database migrations for all features'
        ];

        foreach ($features as $feature => $description) {
            $this->line("  <fg=green>•</> <fg=white>{$feature}:</> {$description}");
        }

        $this->newLine();
    }

    /**
     * Display configuration information
     */
    private function displayConfiguration()
    {
        $this->info('⚙️  CONFIGURATION:');
        $this->newLine();

        $this->line('  <fg=white>Config file:</> config/vormia.php');
        $this->line('  <fg=white>Environment:</> Add VORMIA_TABLE_PREFIX to your .env file');
        $this->line('  <fg=white>Middleware:</> Automatically registered in bootstrap/app.php');
        $this->line('  <fg=white>Providers:</> Service providers auto-registered');

        $this->newLine();
    }

    /**
     * Display troubleshooting information
     */
    private function displayTroubleshooting()
    {
        $this->info('🔧 TROUBLESHOOTING:');
        $this->newLine();

        $this->line('  <fg=white>Issue:</> Installation fails');
        $this->line('  <fg=gray>Solution:</> Ensure PHP 8.2+ and Laravel 12+ requirements are met');
        $this->newLine();

        $this->line('  <fg=white>Issue:</> Middleware not working');
        $this->line('  <fg=gray>Solution:</> Check bootstrap/app.php for middleware registration');
        $this->newLine();

        $this->line('  <fg=white>Issue:</> Migrations fail');
        $this->line('  <fg=gray>Solution:</> Check VORMIA_TABLE_PREFIX in .env file');
        $this->newLine();
    }

    /**
     * Display footer
     */
    private function displayFooter()
    {
        $this->displayFeatures();
        $this->displayConfiguration();
        $this->displayTroubleshooting();

        $this->info('📚 ADDITIONAL RESOURCES:');
        $this->newLine();

        $this->line('  <fg=white>GitHub:</> https://github.com/vormiaphp/vormia');
        $this->line('  <fg=white>Packagist:</> https://packagist.org/packages/vormiaphp/vormia');
        $this->line('  <fg=white>Installation:</> composer require vormiaphp/vormia');

        $this->newLine();
        $this->comment('💡 For more detailed documentation, visit our GitHub repository.');
        $this->newLine();

        $this->info('🎉 Thank you for using Vormia!');
        $this->newLine();
    }
}
