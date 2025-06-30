<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Vormia\Console\Commands\UninstallCommand;

class UninstallCommandTest extends TestCase
{
    public function testUninstallCommandCanBeInstantiated()
    {
        $command = new UninstallCommand();
        $this->assertInstanceOf(UninstallCommand::class, $command);
    }
}
