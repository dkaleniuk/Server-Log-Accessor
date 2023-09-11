<?php

namespace App\Tests\Command;

use App\Command\CalculateTax;
use App\Command\LogLoaderCommand;
use App\Service\Collector;
use App\Service\LogService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class LogLoaderCommandTest extends TestCase
{
    private LogService $mockLogService;
    private Collector $mockCollector;

    public function setUp(): void
    {
        $this->mockLogService = $this->createMock(LogService::class);
        $this->mockCollector = $this->createMock(Collector::class);
    }

    public function testLogLoaderCommand(): void
    {
        $this->configureLogServiceMock();

        $command = new LogLoaderCommand($this->mockLogService, $this->mockCollector);

        $application = new Application();
        $application->add($command);

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);

        $tester->assertCommandIsSuccessful();
    }

    private function configureLogServiceMock(): void
    {
        $this->mockLogService
            ->expects($this->once())
            ->method('deleteExpiredLogs');
    }

    private function configureLogServiceMockWithWrongFile(): void
    {
        $this->mockLogService
            ->expects($this->never())
            ->method('deleteExpiredLogs');
    }
}
