<?php

namespace App\Tests\Service;

use App\Service\Collector;
use App\Service\Reader;
use Doctrine\ORM\EntityManager;
use Kassner\LogParser\LogParser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Finder\SplFileInfo;

class CollectorTest extends TestCase
{
    public function testCollectDirUntilSpecified(): void
    {
        $until = \DateTime::createFromFormat('Y-m-d H:i:s', '2023-01-27 12:00:00');

        $reader = $this->getReaderMock($this->dirLogsGenerator());

        $entityManager = $this->getEntityManagerMock();

        $countGreater = 3;

        $paramsBag = $this->createMock(ContainerBagInterface::class);

        $collector = new Collector($entityManager, $reader, new LogParser(), $paramsBag);
        $stat = $collector->collectDir('validLogDir', $until);

        self::assertEquals($countGreater, array_shift($stat)['succeed']);
    }

    public function testCollectDirUntilNotSpecified()
    {
        $reader = $this->getReaderMock($this->dirLogsGenerator());

        $countLogs = $this->countGeneratorLogs($this->logsGenerator());

        $entityManager = $this->getEntityManagerMock();
        $entityManager->expects($this->exactly($countLogs))->method('persist');

        $paramsBag = $this->createMock(ContainerBagInterface::class);

        $collector  = new Collector($entityManager, $reader, new LogParser(), $paramsBag);
        $stat = $collector->collectDir('validLogDir');

        self::assertEquals($countLogs, array_shift($stat)['succeed']);
    }
    public function testCollectDirInvalidLogFormat()
    {
        $reader = $this->getReaderMock($this->dirLogsGenerator($this->invalidLogFormatGenerator()));
        $paramsBag = $this->createMock(ContainerBagInterface::class);

        $collector  = new Collector($this->getEntityManagerMock(), $reader, new LogParser(), $paramsBag);
        $stat = $collector->collectDir('validLogDir');

        $fileStatistic = array_shift($stat);

        self::assertEquals('Log file probably has invalid format, only CLF allowed', $fileStatistic);
    }

    public function testSkipInvalidLogEntry(): void
    {
        $reader = $this->getReaderMock($this->dirLogsGenerator($this->twoValidAndOtherInvalidLogsGenerator()));

        $paramsBag = $this->createMock(ContainerBagInterface::class);

        $collector  = new Collector($this->getEntityManagerMock(), $reader, new LogParser(), $paramsBag);
        $stat = $collector->collectDir('validLogDir');

        $fileStatistic = array_shift($stat);
        
        self::assertEquals(2, $fileStatistic['succeed']);
    }

    public function testConsecutiveInvalidLogEntriesDifferentLogFiles(): void
    {
        $file1Logs = $this->createGeneratorFromArray(array_merge(
            array_fill(0, 3, $this->validLogLine()),
            array_fill(0, 10, 'invalid log line')
        ));
        $file2Logs = $this->createGeneratorFromArray(array_merge(
            array_fill(0, 11, 'invalid log line'),
            array_fill(0, 3, $this->validLogLine())
        ));
        $reader = $this->getReaderMock($this->dirLogsGenerator($file1Logs, $file2Logs));

        $paramsBag = $this->createMock(ContainerBagInterface::class);

        $collector  = new Collector($this->getEntityManagerMock(), $reader, new LogParser(), $paramsBag);
        $stat = $collector->collectDir('validLogDir');

        foreach ($stat as $fileStatistic) {
            self::assertGreaterThan(0, $fileStatistic['proceed']);
        }
    }

    public function testProceedLogsStat(): void
    {
        $reader = $this->getReaderMock(
            $this->dirLogsGenerator(
                $this->twoValidAndOtherInvalidLogsGenerator(),
                $this->logsGenerator()
            )
        );

        $paramsBag = $this->createMock(ContainerBagInterface::class);

        $collector  = new Collector($this->getEntityManagerMock(), $reader, new LogParser(), $paramsBag);
        $stat = $collector->collectDir('validLogDir');

        $fileStatistic = array_shift($stat);
        $expectedCount = $this->countGeneratorLogs($this->twoValidAndOtherInvalidLogsGenerator());
        $this->assertEquals($expectedCount, $fileStatistic['proceed']);

        $fileStatistic = array_shift($stat);
        $expectedCount = $this->countGeneratorLogs($this->logsGenerator());
        
        self::assertEquals($expectedCount, $fileStatistic['proceed']);
    }

    private function getEntityManagerMock(): EntityManager
    {
        return $this->createMock(EntityManager::class);
    }

    private function getReaderMock($returnValue): Reader
    {
        $reader = $this->createMock(Reader::class);

        $reader
            ->expects($this->once())
            ->method('readDir')
            ->willReturn($returnValue);

        return $reader;
    }

    protected function dirLogsGenerator(): \Generator
    {
        /** @var \Generator[] $logGenerators */
        $logGenerators = func_get_args();
        if (count($logGenerators) === 0) {
            $logGenerators[] = $this->logsGenerator();
        }

        foreach ($logGenerators as $i => $generator) {
            $file = new SplFileInfo('test_'.$i, 'relativePath', 'relativepathname');
            yield $file => $generator;
        }
    }

    protected function invalidLogFormatGenerator(): \Generator
    {
        return $this->createGeneratorFromArray(array_fill(0, 31, 'invalid log line'));
    }

    protected function twoValidAndOtherInvalidLogsGenerator(): \Generator
    {
        $valid = [
            '127.0.0.1 - - [29/Jan/2023:15:24:31 +0200] "GET /logs?datetime=2023-01-29 HTTP/1.1" 400 142',
            '127.0.0.1 - - [28/Jan/2023:15:24:31 +0200] "GET /logs?datetime=2023-01-29 HTTP/1.1" 400 142'
        ];
        $logs = array_merge(array_fill(0, 21, 'invalid log line'), $valid);
        shuffle($logs);

        return $this->createGeneratorFromArray($logs);
    }

    protected function validLogLine(): string
    {
        return '127.0.0.1 - - [29/Jan/2023:15:24:31 +0200] "GET /logs?datetime=2023-01-29 HTTP/1.1" 400 142';
    }

    protected function createGeneratorFromArray(array $array): \Generator
    {
        foreach ($array as $row) {
            yield $row;
        }
    }

    protected function logsGenerator(): \Generator
    {
        $greater = [
            '127.0.0.1 - - [29/Jan/2023:15:24:31 +0200] "GET /app_dev.php/logs?datetime=2023-01-29 HTTP/1.1" 400 142',
            '127.0.0.1 - - [28/Jan/2023:15:24:31 +0200] "GET /app_dev.php/logs?datetime=2023-01-29 HTTP/1.1" 400 142',
            '127.0.0.1 - - [27/Jan/2023:15:24:31 +0200] "GET /app_dev.php/logs?datetime=2023-01-29 HTTP/1.1" 400 142'
        ];
        $less = [
            '127.0.0.1 - - [26/Jan/2023:15:24:31 +0200] "GET /app_dev.php/logs?datetime=2023-01-29 HTTP/1.1" 400 142',
            '127.0.0.1 - - [25/Jan/2023:15:24:31 +0200] "GET /app_dev.php/logs?datetime=2023-01-29 HTTP/1.1" 400 142'
        ];
        $logs = array_merge($greater, $less);

        return $this->createGeneratorFromArray($logs);
    }

    protected function countGeneratorLogs(\Generator $logs): int
    {
        $count = 0;
        while ($logs->valid()) {
            $count++;
            $logs->next();
        }

        return $count;
    }
}
