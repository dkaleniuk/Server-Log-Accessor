<?php

declare(strict_types=1);

namespace App\Factory;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

readonly class JsonResponseFactory
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    public function create(array $data, int $status = 200, array $headers = []): Response
    {
        return new Response(
            $this->serializer->serialize($data, JsonEncoder::FORMAT),
            $status,
            array_merge($headers, ['Content-Type' => 'application/json;charset=UTF-8'])
        );
    }
}
