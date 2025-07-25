<?php

namespace VormiaPHP\Vormia\Tests;

use PHPUnit\Framework\TestCase;
use VormiaPHP\Vormia\VormiaServiceProvider;
use Vormia\Console\Commands\InstallCommand;
use Vormia\Console\Commands\UpdateCommand;

class ServiceProviderTest extends TestCase
{
    /**
     * Test that the service provider class exists and can be reflected
     */
    public function test_service_provider_class_exists()
    {
        $this->assertTrue(class_exists(VormiaServiceProvider::class));

        // Test that we can reflect the class
        $reflection = new \ReflectionClass(VormiaServiceProvider::class);
        $this->assertTrue($reflection->isSubclassOf(\Illuminate\Support\ServiceProvider::class));
    }

    /**
     * Test that the install command can be instantiated
     */
    public function test_install_command_can_be_instantiated()
    {
        $command = new InstallCommand();
        $this->assertInstanceOf(InstallCommand::class, $command);
    }

    /**
     * Test that the update command can be instantiated
     */
    public function test_update_command_can_be_instantiated()
    {
        $command = new UpdateCommand();
        $this->assertInstanceOf(UpdateCommand::class, $command);
    }

    /**
     * Test that the service provider has the correct config file reference
     */
    public function test_service_provider_has_correct_config_reference()
    {
        // Test that the config file path is correct
        $configPath = __DIR__ . '/../src/config/vormia.php';
        $this->assertFileExists($configPath, 'Config file vormia.php should exist');

        // Test that the service provider class can be reflected
        $reflection = new \ReflectionClass(VormiaServiceProvider::class);
        $this->assertTrue($reflection->hasMethod('register'));
        $this->assertTrue($reflection->hasMethod('boot'));
    }

    /**
     * Test that the config file exists
     */
    public function test_config_file_exists()
    {
        $configPath = __DIR__ . '/../src/config/vormia.php';
        $this->assertFileExists($configPath, 'Config file vormia.php should exist');
    }

    /**
     * Test that the config file is valid PHP
     */
    public function test_config_file_is_valid_php()
    {
        $configPath = __DIR__ . '/../src/config/vormia.php';
        $config = require $configPath;

        $this->assertIsArray($config, 'Config should return an array');
        $this->assertArrayHasKey('table_prefix', $config, 'Config should have table_prefix key');
        $this->assertArrayHasKey('auto_update_slugs', $config, 'Config should have auto_update_slugs key');
        $this->assertArrayHasKey('mediaforge', $config, 'Config should have mediaforge key');
    }
}
