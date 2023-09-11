<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Response\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends AbstractController
{
    protected function createResponse(mixed $content, array $context = [], int $statusCode = Response::HTTP_OK): ApiResponse
    {
        return new ApiResponse($content, $context, $statusCode);
    }
}
