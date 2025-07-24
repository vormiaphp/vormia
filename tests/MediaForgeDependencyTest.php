<?php

namespace VormiaPHP\Vormia\Tests;

use PHPUnit\Framework\TestCase;
use App\Services\Vrm\MediaForgeService;

class MediaForgeDependencyTest extends TestCase
{
    /**
     * Test that MediaForgeService checks for intervention/image dependency
     */
    public function test_mediaforge_requires_intervention_image()
    {
        // Mock the class_exists function to return false
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The intervention/image package is required for MediaForgeService');

        // This should throw an exception if intervention/image is not available
        new MediaForgeService();
    }

    /**
     * Test the static method to check dependency availability
     */
    public function test_is_image_processing_available()
    {
        $isAvailable = MediaForgeService::isImageProcessingAvailable();
        $this->assertIsBool($isAvailable);
    }

    /**
     * Test the installation instructions method
     */
    public function test_get_installation_instructions()
    {
        $instructions = MediaForgeService::getInstallationInstructions();
        $this->assertStringContainsString('composer require intervention/image', $instructions);
    }
}
