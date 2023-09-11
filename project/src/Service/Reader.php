<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\LogException;
use Symfony\Component\Finder\Finder;

class Reader
{
    public function readDir(string $logDir): \Generator
    {
        foreach ($this->getDirFiles($logDir) as $file) {
            try {
                yield $file => $this->readFile($file);
            } catch (LogException) {
                continue;
            }
        }
    }

    public function readFile(\SplFileInfo $file): \Generator
    {
        $this->assertValidFile($file);

        try {
            $openedFile = $file->openFile();
            foreach ($this->readLines($openedFile) as $line) {
                yield $line;
            }
        } finally {
            $openedFile = null;
        }
    }

    private function readLines(\SplFileObject $file): \Generator
    {
        $pos = -1;
        $currentLine = '';
        while (-1 !== $file->fseek($pos, SEEK_END)) {
            $char = $file->fgetc();
            if (PHP_EOL === $char) {
                yield $currentLine;
                $currentLine = '';
            } else {
                $currentLine = $char.$currentLine;
            }
            --$pos;
        }
        if (strlen($currentLine) > 0) {
            yield $currentLine;
        }
    }

    private function assertValidFile(\SplFileInfo|string $file): void
    {
        if (!$file instanceof \SplFileInfo) {
            $file = new \SplFileInfo($file);
        }
        if ('log' !== $file->getExtension()) {
            throw LogException::notLogFile($file);
        }
        if (!$file->isReadable()) {
            throw LogException::notReadable($file);
        }
    }

    private function getDirFiles(string $logDir): Finder
    {
        $finder = new Finder();
        $finder->ignoreDotFiles(true)->ignoreVCS(true);
        $finder->files()->in($logDir)->name('*.log');

        return $finder;
    }
}
