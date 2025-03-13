<?php

namespace VormiaCms\StarterKit\Console;

use Illuminate\Console\Command;
use VormiaCms\StarterKit\VormiaStarterKit;

class InstallCommand extends Command
{
    protected $signature = 'vormia:install';

    protected $description = 'Install Vormia CMS Starter Kit';

    public function handle()
    {
        $this->info('Installing Vormia CMS Starter Kit...');

        $starter = new VormiaStarterKit();
        $starter->install();

        // Add .gitignore entries
        $this->appendToGitIgnore([
            '# Custom Ignore',
            '.DS_Store',
            '/storage/app/public/media',
            '/public/media'
        ]);

        $this->info('Vormia CMS Starter Kit has been installed successfully!');
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
}
