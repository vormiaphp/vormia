<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Vormia\Console\Commands\HelpCommand;

class HelpCommandTest extends TestCase
{
    public function testHelpCommandCanBeInstantiated()
    {
        $command = new HelpCommand();
        $this->assertInstanceOf(HelpCommand::class, $command);
    }
}
