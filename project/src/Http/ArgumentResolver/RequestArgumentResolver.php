<?php

declare(strict_types=1);

namespace App\Http\ArgumentResolver;

use App\Http\DTO\Request\RequestDTOInterface;
use App\Http\RequestTransformer\RequestTransformer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class RequestArgumentResolver implements ValueResolverInterface
{
    public function __construct(private readonly RequestTransformer $requestTransformer)
    {
    }

    /**
     * @throws \ReflectionException
     */
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if (!\class_exists($argument->getType())) {
            return false;
        }

        return (new \ReflectionClass($argument->getType()))->implementsInterface(RequestDTOInterface::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        $this->requestTransformer->transform($request);

        $class = $argument->getType();

        yield new $class($request);
    }
}
