<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Collector;
use App\Service\LogService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:logs-grabber',
    description: 'Collects *.log files into mysql-cache table.'
)]
class LogLoaderCommand extends Command
{
    public function __construct(
        private readonly LogService $logService,
        private readonly Collector $collector,
        private readonly string $defaultLogDir = '',
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'logDir',
                InputArgument::OPTIONAL,
                'Specify dir where logs are located'
            )
            ->addOption(
                'keepMax',
                null,
                InputOption::VALUE_REQUIRED,
                'Max date range when logs will be cached',
                '1 week'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logDir = $input->getArgument('logDir');
        $logDir = $logDir ?: $this->defaultLogDir;

        $keepMax = $input->getOption('keepMax');

        $this->logService->deleteExpiredLogs($keepMax);

        $collectionInfo = $this->collector->collectDir($logDir, $this->logService->getLastLogUpdate($keepMax));

        $output->write(json_encode($collectionInfo, JSON_PRETTY_PRINT));

        return Command::SUCCESS;
    }
}
