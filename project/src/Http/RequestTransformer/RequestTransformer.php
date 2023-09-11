<?php

declare(strict_types=1);

namespace App\Http\RequestTransformer;

use App\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

final class RequestTransformer
{
    private const METHODS_TO_TRANSFORM = [
        Request::METHOD_POST,
    ];

    public function transform(Request $request): void
    {
        if (!\in_array($request->getMethod(), self::METHODS_TO_TRANSFORM, true)) {
            return;
        }

        try {
            if (\strlen($request->getContent()) > 0) {
                $request->request = new ParameterBag((array) \json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR));
            }
        } catch (\JsonException $e) {
            throw InvalidArgumentException::createFromMessage('Invalid JSON payload');
        }
    }
}
