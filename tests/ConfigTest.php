<?php

namespace VormiaPHP\Vormia\Tests;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * Test that the config file can be loaded without errors
     */
    public function test_config_file_can_be_loaded()
    {
        $configPath = __DIR__ . '/../src/config/vormia.php';

        // This should not throw any errors
        $config = require $configPath;

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);
    }

    /**
     * Test that the config has the expected structure
     */
    public function test_config_has_expected_structure()
    {
        $configPath = __DIR__ . '/../src/config/vormia.php';
        $config = require $configPath;

        // Test top-level keys
        $this->assertArrayHasKey('table_prefix', $config);
        $this->assertArrayHasKey('auto_update_slugs', $config);
        $this->assertArrayHasKey('slug_approval_required', $config);
        $this->assertArrayHasKey('slug_history_enabled', $config);
        $this->assertArrayHasKey('mediaforge', $config);

        // Test mediaforge sub-array
        $this->assertIsArray($config['mediaforge']);
        $this->assertArrayHasKey('driver', $config['mediaforge']);
        $this->assertArrayHasKey('default_quality', $config['mediaforge']);
        $this->assertArrayHasKey('default_format', $config['mediaforge']);
        $this->assertArrayHasKey('auto_override', $config['mediaforge']);
        $this->assertArrayHasKey('preserve_originals', $config['mediaforge']);
    }

    /**
     * Test that the config values are of correct types
     */
    public function test_config_values_are_correct_types()
    {
        $configPath = __DIR__ . '/../src/config/vormia.php';
        $config = require $configPath;

        // Test data types
        $this->assertIsString($config['table_prefix']);
        $this->assertIsBool($config['auto_update_slugs']);
        $this->assertIsBool($config['slug_approval_required']);
        $this->assertIsBool($config['slug_history_enabled']);
        $this->assertIsArray($config['mediaforge']);

        // Test mediaforge values
        $this->assertIsString($config['mediaforge']['driver']);
        $this->assertIsInt($config['mediaforge']['default_quality']);
        $this->assertIsString($config['mediaforge']['default_format']);
        $this->assertIsBool($config['mediaforge']['auto_override']);
        $this->assertIsBool($config['mediaforge']['preserve_originals']);
    }
}
