<?php

declare(strict_types=1);

namespace App\Service;

use App\Http\DTO\Input\Log\GetLogInputDTO;
use App\Repository\LogEntryRepositoryInterface;

class LogService
{
    public function __construct(
        private readonly LogEntryRepositoryInterface $repository
    ) {
    }

    public function getLogs(GetLogInputDTO $inputDTO): array
    {
        return $this->repository->findLogsByQueryData($inputDTO);
    }

    public function deleteExpiredLogs(string $keepMax): void
    {
        $this->repository->deleteLessThan($this->getLastActualLogDate($keepMax));
    }

    public function getLastLogUpdate(string $keepMax): ?\DateTime
    {
        $lastUpdate = $this->repository->getLastLogUpdate();

        if (!$lastUpdate) {
            return null;
        }

        $lastUpdate = \DateTime::createFromFormat('Y-m-d H:i:s', $lastUpdate);
        $lastActual = $this->getLastActualLogDate($keepMax);

        if ($lastUpdate < $lastActual) {
            $lastUpdate = $lastActual;
        }

        return $lastUpdate;
    }

    private function getLastActualLogDate(string $keepMax): \DateTime
    {
        return (new \DateTime())->sub(\DateInterval::createFromDateString($keepMax));
    }
}
