<?php

declare(strict_types=1);

namespace App\Validator;

trait AssertLimitTrait
{
    private const ALLOWED_VALUES = ['10', '25', '50', '100'];

    private function assertLimitIsValid(string $limit): bool
    {
        return \in_array($limit, self::ALLOWED_VALUES, true);
    }
}
