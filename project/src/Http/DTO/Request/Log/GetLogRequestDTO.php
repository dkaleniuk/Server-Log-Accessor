<?php

declare(strict_types=1);

namespace App\Http\DTO\Request\Log;

use App\Http\DTO\Request\RequestDTOInterface;
use Symfony\Component\HttpFoundation\Request;

class GetLogRequestDTO implements RequestDTOInterface
{
    private const ALLOWED_QUERY_PARAMS = [
        'limit',
        'page',
        'textRegex',
        'textLike',
        'dateTimeBetween',
    ];

    private ?string $limit = null;
    private ?string $page = null;
    private array $textRegex = [];
    private array $textLike = [];
    private array $dateTimeBetween = [];

    public function __construct(Request $request)
    {
        foreach ($request->query->all() as $parameterName => $queryParam) {
            if (\in_array($parameterName, self::ALLOWED_QUERY_PARAMS, true)) {
                $this->{$parameterName} = $queryParam;
            }
        }
    }

    public function getLimit(): ?string
    {
        return $this->limit;
    }

    public function getPage(): ?string
    {
        return $this->page;
    }

    public function getTextLike(): ?array
    {
        return $this->textLike;
    }

    public function getTextRegex(): ?array
    {
        return $this->textRegex;
    }

    public function getDateTimeBetween(): ?array
    {
        return $this->dateTimeBetween;
    }
}
