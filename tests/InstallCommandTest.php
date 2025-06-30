<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Illuminate\Console\Application as Artisan;
use Vormia\Console\Commands\InstallCommand;

class InstallCommandTest extends TestCase
{
    public function testInstallCommandCanBeInstantiated()
    {
        $command = new InstallCommand();
        $this->assertInstanceOf(InstallCommand::class, $command);
    }

    // More functional tests would require a Laravel test environment
}
