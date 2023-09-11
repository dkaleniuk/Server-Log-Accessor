<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\LogEntry;
use Doctrine\ORM\EntityManagerInterface;
use Kassner\LogParser\FormatException;
use Kassner\LogParser\LogParser;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class Collector
{
    private int $consecutiveInvalid = 0;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Reader $reader,
        private readonly LogParser $parser,
        private readonly ContainerBagInterface $params,
    ) {
    }

    public function collectDir(string $logDir, \DateTime $until = null): array
    {
        $collectionInfo = [];
        /** @var $file \SplFileInfo */
        foreach ($this->reader->readDir($logDir) as $file => $fileRows) {
            try {
                $collectionInfo[$file->getFilename()] = $this->collectFile($fileRows, $until);
            } catch (FormatException $e) {
                $collectionInfo[$file->getFilename()] = $e->getMessage();
            }
        }

        return $collectionInfo;
    }

    private function collectFile(\Generator $fileRows, \DateTime $until = null): array
    {
        $collectionInfo = [
            'proceed' => 0,
            'succeed' => 0,
        ];

        $this->consecutiveInvalid = 0;

        foreach ($fileRows as $row) {
            ++$collectionInfo['proceed'];

            $logEntry = $this->createLogEntry($row);

            if (!$this->isLogEntryValid($logEntry)) {
                continue;
            }

            if (!$this->shouldContinue($logEntry, $until)) {
                break;
            }

            $this->entityManager->persist($logEntry);
            ++$collectionInfo['succeed'];

            $batchSize = $this->params->get('app.collector_batch_size') ?: 100;

            if ($collectionInfo['succeed'] % $batchSize === 0) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();

        return $collectionInfo;
    }

    private function isLogEntryValid(bool|LogEntry $logEntry): bool
    {
        if (!$logEntry instanceof LogEntry) {
            ++$this->consecutiveInvalid;

            $invalidRowsCount = $this->params->get('app.invalid_lines_count_to_stop_parser') ?: 30;
            if ($this->consecutiveInvalid > $invalidRowsCount) {
                throw new FormatException('Log file probably has invalid format, only CLF allowed');
            }

            return false;
        }
        $this->consecutiveInvalid = 0;

        return true;
    }

    private function shouldContinue(LogEntry $logEntry, \DateTime $until = null): bool
    {
        if (!$until instanceof \DateTime) {
            return true;
        }

        return $logEntry->getDatetime() > $until;
    }

    private function createLogEntry(string $stringRow): false|LogEntry
    {
        try {
            $parsed = $this->parser->parse($stringRow);
        } catch (FormatException $e) {
            return false;
        }

        $logEntry = new LogEntry();

        $logEntry->setDatetime((new \DateTime())->setTimestamp(strtotime($parsed->time)));
        $logEntry->setText($stringRow);

        return $logEntry;
    }
}
