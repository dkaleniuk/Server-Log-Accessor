<?php

declare(strict_types=1);

namespace App\Controller;

use App\Factory\JsonResponseFactory;
use App\Http\DTO\Input\Log\GetLogInputDTO;
use App\Http\DTO\Request\Log\GetLogRequestDTO;
use App\Service\LogService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LogController extends ApiController
{
    public function __construct(
        private readonly JsonResponseFactory $jsonResponseFactory,
        private readonly LogService $logService
    ) {
    }

    #[Route(path: '/api/v1/logs', name: 'get_logs', methods: Request::METHOD_GET)]
    public function __invoke(GetLogRequestDTO $requestDTO): Response
    {
        // Check authorization!

        $logs = $this->logService->getLogs(GetLogInputDTO::create(
            $requestDTO->getPage(),
            $requestDTO->getLimit(),
            $requestDTO->getTextLike(),
            $requestDTO->getTextRegex(),
            $requestDTO->getDateTimeBetween(),
        ));

        return $this->jsonResponseFactory->create($logs);
    }
}
