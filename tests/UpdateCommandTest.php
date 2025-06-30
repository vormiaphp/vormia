<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Vormia\Console\Commands\UpdateCommand;

class UpdateCommandTest extends TestCase
{
    public function testUpdateCommandCanBeInstantiated()
    {
        $command = new UpdateCommand();
        $this->assertInstanceOf(UpdateCommand::class, $command);
    }
}
