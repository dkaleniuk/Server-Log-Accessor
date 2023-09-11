<?php

namespace App\Exception;

class LogException extends \Exception
{
    public static function notLogFile(\SplFileInfo $file): self
    {
        return new self("File '{$file->getFilename()}' is not a log file");
    }

    public static function notReadable(\SplFileInfo $file): self
    {
        return new self("File '{$file->getFilename()}' is not readable");
    }

    public static function unknownFilterOperator(string $operator): self
    {
        $stringOperator = self::toString($operator);

        return new self('Unknown filter operator '.($stringOperator ? "'$stringOperator'" : ''));
    }

    public static function invalidFilterValue(string $name): self
    {
        $nameStr = self::toString($name);

        return new self('Filter "'.$nameStr.'" has not valid value. Scalar or array of scalar expected"');
    }

    public static function oneDimensionName(string $name): self
    {
        $nameStr = self::toString($name);

        return new self('Filter "'.$nameStr.'" must have only one dimension filter name"');
    }

    public static function invalidQbAlias(): self
    {
        return new self("Can't create Filter due to invalid QueryBuilder alias. Only one alias expected");
    }

    private static function toString($value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
