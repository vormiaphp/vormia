<?php

namespace VormiaPHP\Vormia\Tests;

use PHPUnit\Framework\TestCase;

class MediaForgeCodeValidationTest extends TestCase
{
    /**
     * Test that MediaForgeService file exists and has valid PHP syntax
     */
    public function test_mediaforge_service_file_exists_and_valid()
    {
        $serviceFile = __DIR__ . '/../src/stubs/services/Vrm/MediaForgeService.php';
        $this->assertFileExists($serviceFile);

        // Check if file has valid PHP syntax
        $output = [];
        $returnCode = 0;
        exec("php -l $serviceFile 2>&1", $output, $returnCode);

        $this->assertEquals(0, $returnCode, "PHP syntax error in MediaForgeService.php: " . implode("\n", $output));
    }

    /**
     * Test that MediaForge facade file exists and has valid PHP syntax
     */
    public function test_mediaforge_facade_file_exists_and_valid()
    {
        $facadeFile = __DIR__ . '/../src/stubs/facades/Vrm/MediaForge.php';
        $this->assertFileExists($facadeFile);

        // Check if file has valid PHP syntax
        $output = [];
        $returnCode = 0;
        exec("php -l $facadeFile 2>&1", $output, $returnCode);

        $this->assertEquals(0, $returnCode, "PHP syntax error in MediaForge.php: " . implode("\n", $output));
    }

    /**
     * Test that MediaForgeService has required methods for delete functionality
     */
    public function test_mediaforge_service_has_delete_methods()
    {
        $serviceFile = __DIR__ . '/../src/stubs/services/Vrm/MediaForgeService.php';
        $content = file_get_contents($serviceFile);

        // Check for delete method
        $this->assertStringContainsString('public function delete(', $content);

        // Check for delete preview method
        $this->assertStringContainsString('public function getDeletePreview(', $content);

        // Check for file pattern methods
        $this->assertStringContainsString('protected function findFilesByType(', $content);
        $this->assertStringContainsString('protected function isRelatedFile(', $content);
        $this->assertStringContainsString('protected function isThumbnailFile(', $content);
        $this->assertStringContainsString('protected function isResizedFile(', $content);
        $this->assertStringContainsString('protected function isCompressedFile(', $content);
        $this->assertStringContainsString('protected function isConvertedFile(', $content);
        $this->assertStringContainsString('protected function isWatermarkedFile(', $content);
        $this->assertStringContainsString('protected function isAvatarFile(', $content);

        // Check for file pattern generation
        $this->assertStringContainsString('public function getFilePatterns(', $content);

        // Check for path validation methods
        $this->assertStringContainsString('public function normalizeFilePath(', $content);
        $this->assertStringContainsString('public function isValidFilePath(', $content);
    }

    /**
     * Test that MediaForgeService has proper error handling
     */
    public function test_mediaforge_service_has_error_handling()
    {
        $serviceFile = __DIR__ . '/../src/stubs/services/Vrm/MediaForgeService.php';
        $content = file_get_contents($serviceFile);

        // Check for exception handling
        $this->assertStringContainsString('try {', $content);
        $this->assertStringContainsString('} catch (\\Exception $e)', $content);

        // Check for logging
        $this->assertStringContainsString('Log::', $content);

        // Check for validation
        $this->assertStringContainsString('throw new \\InvalidArgumentException', $content);
        $this->assertStringContainsString('throw new \\RuntimeException', $content);
    }

    /**
     * Test that MediaForgeService has proper driver handling
     */
    public function test_mediaforge_service_has_driver_handling()
    {
        $serviceFile = __DIR__ . '/../src/stubs/services/Vrm/MediaForgeService.php';
        $content = file_get_contents($serviceFile);

        // Check for driver methods
        $this->assertStringContainsString('public static function isImagickAvailable()', $content);
        $this->assertStringContainsString('public static function isGdAvailable()', $content);
        $this->assertStringContainsString('public static function getAvailableDrivers()', $content);
        $this->assertStringContainsString('public function getCurrentDriver()', $content);
        $this->assertStringContainsString('public function setDriver(', $content);
    }

    /**
     * Test that MediaForgeService has proper operation support checking
     */
    public function test_mediaforge_service_has_operation_support()
    {
        $serviceFile = __DIR__ . '/../src/stubs/services/Vrm/MediaForgeService.php';
        $content = file_get_contents($serviceFile);

        // Check for operation support methods
        $this->assertStringContainsString('public function isOperationSupported(', $content);
        $this->assertStringContainsString('public function getOperationWarnings(', $content);

        // Check for specific operations
        $this->assertStringContainsString("case 'watermark':", $content);
        $this->assertStringContainsString("case 'convert':", $content);
        $this->assertStringContainsString("case 'avatar':", $content);
        $this->assertStringContainsString("case 'progressive':", $content);
    }

    /**
     * Test that MediaForge facade has proper documentation
     */
    public function test_mediaforge_facade_has_documentation()
    {
        $facadeFile = __DIR__ . '/../src/stubs/facades/Vrm/MediaForge.php';
        $content = file_get_contents($facadeFile);

        // Check for proper documentation
        $this->assertStringContainsString('/**', $content);
        $this->assertStringContainsString('MediaForge Facade', $content);
        $this->assertStringContainsString('image processing', $content);
        $this->assertStringContainsString('@method', $content);
        $this->assertStringContainsString('delete(', $content);
        $this->assertStringContainsString('getDeletePreview(', $content);
    }

    /**
     * Test that MediaForgeService has proper configuration handling
     */
    public function test_mediaforge_service_has_configuration_handling()
    {
        $serviceFile = __DIR__ . '/../src/stubs/services/Vrm/MediaForgeService.php';
        $content = file_get_contents($serviceFile);

        // Check for configuration methods
        $this->assertStringContainsString('public static function getDefaultQuality()', $content);
        $this->assertStringContainsString('public static function getDefaultFormat()', $content);
        $this->assertStringContainsString('public static function getAutoOverride()', $content);
        $this->assertStringContainsString('public static function getPreserveOriginals()', $content);
        $this->assertStringContainsString('public static function getConfiguredDriver()', $content);
    }

    /**
     * Test that MediaForgeService has proper file operations
     */
    public function test_mediaforge_service_has_file_operations()
    {
        $serviceFile = __DIR__ . '/../src/stubs/services/Vrm/MediaForgeService.php';
        $content = file_get_contents($serviceFile);

        // Check for file operation methods
        $this->assertStringContainsString('public function upload(', $content);
        $this->assertStringContainsString('public function uploadFromUrl(', $content);
        $this->assertStringContainsString('public function resize(', $content);
        $this->assertStringContainsString('public function compress(', $content);
        $this->assertStringContainsString('public function convert(', $content);
        $this->assertStringContainsString('public function thumbnail(', $content);
        $this->assertStringContainsString('public function watermark(', $content);
        $this->assertStringContainsString('public function makeAvatar(', $content);
    }

    /**
     * Test that MediaForgeService has proper chaining support
     */
    public function test_mediaforge_service_has_chaining_support()
    {
        $serviceFile = __DIR__ . '/../src/stubs/services/Vrm/MediaForgeService.php';
        $content = file_get_contents($serviceFile);

        // Check for chaining methods that return self
        $this->assertStringContainsString('public function setDriver(', $content);
        $this->assertStringContainsString('public function setDisk(', $content);
        $this->assertStringContainsString('public function setVisibility(', $content);
        $this->assertStringContainsString('public function setFolder(', $content);
        $this->assertStringContainsString('public function useYearFolder(', $content);
        $this->assertStringContainsString('public function randomizeFileName(', $content);
        $this->assertStringContainsString('public function privateUpload(', $content);
    }
}
