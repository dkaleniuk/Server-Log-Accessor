<?php

namespace App\Tests\Service;

use App\Exception\LogException;
use App\Service\Reader;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    public function testCreate(): void
    {
        self::assertInstanceOf('App\Service\Reader', new Reader());
    }

    public function testReadEmptyDir(): void
    {
        $reader = new Reader();
        $records = $reader->readDir($this->dir('DirWithoutLogs'));

        self::assertCount(0, iterator_to_array($records));
    }

    public function testDirWithoutLogFiles(): void
    {
        $reader = new Reader();
        $records = $reader->readDir($this->dir('DirWithoutLogs'));

        $this->assertCount(0, iterator_to_array($records));

        $records = $reader->readDir($this->dir('Logs'));

        $this->assertGreaterThan(0, count(iterator_to_array($records, false)));
    }

    public function testIsNotLogFile(): void
    {
        $this->expectException(LogException::class);
        $reader = new Reader();
        $records = $reader->readFile($this->fileInfo('Logs/not_log_file.txt'));

        iterator_to_array($records);
    }

    public function testEmptyFile(): void
    {
        $this->expectException(LogException::class);
        $reader = new Reader();
        $records = $reader->readFile($this->fileInfo('Logs/empty_Log.log'));

        $this->assertCount(0, iterator_to_array($records));
    }

    private function dir(string $dirName): string
    {
        return $this->getFixturesPath() . $dirName;
    }

    private function file(string $fileName): string
    {
        return $this->getFixturesPath() . $fileName;
    }

    private function fileInfo(string $fileName): \SplFileInfo
    {
        return new \SplFileInfo($this->file(str_replace('/', DIRECTORY_SEPARATOR, $fileName)));
    }

    private function getFixturesPath(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR . '../Fixtures' . DIRECTORY_SEPARATOR;
    }
}
