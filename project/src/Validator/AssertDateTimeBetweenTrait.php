<?php

declare(strict_types=1);

namespace App\Validator;

trait AssertDateTimeBetweenTrait
{
    private function assertDateTimeBetweenIsValid(string $dateFrom, string $dateTo): bool
    {
        return \DateTime::createFromFormat('Y-m-d H:i:s', $dateFrom) <= \DateTime::createFromFormat('Y-m-d H:i:s', $dateTo);
    }
}
