<?php

declare(strict_types=1);

namespace App\Http\DTO\Input\Log;

use App\Validator\AssertDateTimeBetweenTrait;
use App\Validator\AssertLimitTrait;

class GetLogInputDTO
{
    use AssertDateTimeBetweenTrait;
    use AssertLimitTrait;

    private const DEFAULT_PAGE = 1;
    private const DEFAULT_LIMIT = 10;

    private int $page;
    private int $limit;
    private array $textLike;
    private array $textRegex;
    private array $dateTimeBetween;

    private function __construct(
        ?string $page,
        ?string $limit,
        array $textLike,
        array $textRegex,
        array $dateTimeBetween,
    ) {
        if (!\is_numeric($page)) {
            $page = self::DEFAULT_PAGE;
        }

        if (!\is_numeric($limit)) {
            $limit = self::DEFAULT_LIMIT;
        }

        if (!$this->assertLimitIsValid((string) $limit)) {
            $limit = self::DEFAULT_LIMIT;
        }

        $validDateTimeBetween = [];
        foreach ($dateTimeBetween as $key => $values) {
            list($from, $to) = explode(',', $values);

            if ($this->assertDateTimeBetweenIsValid($from, $to)) {
                $validDateTimeBetween[] = $values;
            }
        }

        // Any custom validation could happen here!

        $this->page = (int) $page;
        $this->limit = (int) $limit;
        $this->textLike = $textLike;
        $this->textRegex = $textRegex;
        $this->dateTimeBetween = $validDateTimeBetween;
    }

    public static function create(
        ?string $page,
        ?string $limit,
        array $textLike,
        array $textRegex,
        array $dateTimeBetween,
    ): self {
        return new self(
            $page,
            $limit,
            $textLike,
            $textRegex,
            $dateTimeBetween
        );
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getTextLike(): array
    {
        return $this->textLike;
    }

    public function getTextRegex(): array
    {
        return $this->textRegex;
    }

    public function getDateTimeBetween(): array
    {
        return $this->dateTimeBetween;
    }
}
