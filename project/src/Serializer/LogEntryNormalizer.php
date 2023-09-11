<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Entity\LogEntry;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

class LogEntryNormalizer implements NormalizerInterface
{
    use SerializerAwareTrait;

    public function normalize(mixed $object, $format = null, array $context = []): array
    {
        return [
            'id' => $object->getId(),
            'datetime' => $object->getDatetime()->format('Y-m-d H:i:s'),
            'text' => $object->getText(),
        ];
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof LogEntry;
    }
}
