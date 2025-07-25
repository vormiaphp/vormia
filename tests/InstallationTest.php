<?php

namespace VormiaPHP\Vormia\Tests;

use PHPUnit\Framework\TestCase;
use Vormia\Console\Commands\InstallCommand;
use Vormia\Console\Commands\UpdateCommand;

class InstallationTest extends TestCase
{
    /**
     * Test that the install command has the correct signature
     */
    public function test_install_command_signature()
    {
        $command = new InstallCommand();
        $reflection = new \ReflectionClass($command);
        $signatureProperty = $reflection->getProperty('signature');
        $signatureProperty->setAccessible(true);
        $signature = $signatureProperty->getValue($command);
        $this->assertEquals('vormia:install {--api : Install with API support (requires Sanctum, see instructions)}', $signature);
    }

    /**
     * Test that the update command has the correct signature
     */
    public function test_update_command_signature()
    {
        $command = new UpdateCommand();
        $reflection = new \ReflectionClass($command);
        $signatureProperty = $reflection->getProperty('signature');
        $signatureProperty->setAccessible(true);
        $signature = $signatureProperty->getValue($command);
        $this->assertEquals('vormia:update {--force : Skip confirmation prompts}', $signature);
    }

    /**
     * Test that the install command has the correct description
     */
    public function test_install_command_description()
    {
        $command = new InstallCommand();
        $reflection = new \ReflectionClass($command);
        $descriptionProperty = $reflection->getProperty('description');
        $descriptionProperty->setAccessible(true);
        $description = $descriptionProperty->getValue($command);
        $this->assertEquals('Install Vormia package with all necessary files and configurations', $description);
    }

    /**
     * Test that the update command has the correct description
     */
    public function test_update_command_description()
    {
        $command = new UpdateCommand();
        $reflection = new \ReflectionClass($command);
        $descriptionProperty = $reflection->getProperty('description');
        $descriptionProperty->setAccessible(true);
        $description = $descriptionProperty->getValue($command);
        $this->assertEquals('Update Vormia package files (removes old files and copies fresh ones)', $description);
    }

    /**
     * Test that the install command uses the correct service provider
     */
    public function test_install_command_uses_correct_service_provider()
    {
        $command = new InstallCommand();

        // Use reflection to check the handle method
        $reflection = new \ReflectionClass($command);
        $handleMethod = $reflection->getMethod('handle');
        $handleMethod->setAccessible(true);

        // Get the method source code to check for the correct service provider
        $filename = $reflection->getFileName();
        $startLine = $handleMethod->getStartLine();
        $endLine = $handleMethod->getEndLine();

        $source = file_get_contents($filename);
        $lines = explode("\n", $source);
        $methodSource = implode("\n", array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

        // Check that it uses the correct service provider
        $this->assertStringContainsString('VormiaPHP\\Vormia\\VormiaServiceProvider', $methodSource);
        $this->assertStringNotContainsString('Vormia\\VormiaServiceProvider', $methodSource);
    }

    /**
     * Test that the update command uses the correct service provider
     */
    public function test_update_command_uses_correct_service_provider()
    {
        $command = new UpdateCommand();

        // Use reflection to check the publishFreshFiles method
        $reflection = new \ReflectionClass($command);
        $publishMethod = $reflection->getMethod('publishFreshFiles');
        $publishMethod->setAccessible(true);

        // Get the method source code to check for the correct service provider
        $filename = $reflection->getFileName();
        $startLine = $publishMethod->getStartLine();
        $endLine = $publishMethod->getEndLine();

        $source = file_get_contents($filename);
        $lines = explode("\n", $source);
        $methodSource = implode("\n", array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

        // Check that it uses the correct service provider
        $this->assertStringContainsString('VormiaPHP\\Vormia\\VormiaServiceProvider', $methodSource);
        $this->assertStringNotContainsString('Vormia\\VormiaServiceProvider', $methodSource);
    }

    /**
     * Test that the config file structure is correct
     */
    public function test_config_file_structure()
    {
        $configPath = __DIR__ . '/../src/config/vormia.php';
        $config = require $configPath;

        // Test required keys
        $requiredKeys = [
            'table_prefix',
            'auto_update_slugs',
            'slug_approval_required',
            'slug_history_enabled',
            'mediaforge'
        ];

        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $config, "Config should have key: $key");
        }

        // Test mediaforge structure
        $this->assertIsArray($config['mediaforge'], 'mediaforge should be an array');
        $this->assertArrayHasKey('driver', $config['mediaforge']);
        $this->assertArrayHasKey('default_quality', $config['mediaforge']);
        $this->assertArrayHasKey('default_format', $config['mediaforge']);
        $this->assertArrayHasKey('auto_override', $config['mediaforge']);
        $this->assertArrayHasKey('preserve_originals', $config['mediaforge']);
    }

    /**
     * Test that the service provider file structure is correct
     */
    public function test_service_provider_structure()
    {
        $providerPath = __DIR__ . '/../src/VormiaServiceProvider.php';
        $this->assertFileExists($providerPath, 'Service provider should exist');

        $source = file_get_contents($providerPath);

        // Check that it references the correct config file
        $this->assertStringContainsString("'/config/vormia.php'", $source);
        $this->assertStringNotContainsString("'/config/vrm.php'", $source);

        // Check that it has the correct publish tags
        $this->assertStringContainsString("'vormia-config'", $source);
        $this->assertStringContainsString("'vormia-migrations'", $source);
        $this->assertStringContainsString("'vormia-files'", $source);
        $this->assertStringContainsString("'vormia-stubs'", $source);
    }
}
