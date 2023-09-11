<?php

namespace App\Http\Response;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiResponse implements ResponseInterface
{
    private mixed $content;
    private array $context;
    private int $statusCode;
    private array $headers;

    public function __construct(mixed $content, array $context = [], int $statusCode = Response::HTTP_OK, array $headers = [])
    {
        $this->content = $content;
        $this->context = $context;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function getContent(bool $throw = true): string
    {
        return $this->content;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(bool $throw = true): array
    {
        return $this->headers;
    }

    public function toArray(bool $throw = true): array
    {
        // TODO: Implement toArray() method.
    }

    public function cancel(): void
    {
        // TODO: Implement cancel() method.
    }

    public function getInfo(string $type = null)
    {
        // TODO: Implement getInfo() method.
    }
}
