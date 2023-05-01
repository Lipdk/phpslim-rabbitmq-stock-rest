<?php

namespace App\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Services\Queue\ConsumerService;

class ConsumerCommand extends Command
{
    /** @var InputInterface */
    protected InputInterface $input;

    /** @var OutputInterface  */
    protected OutputInterface $output;

    protected ConsumerService $consumerService;

    /**
     * @param ConsumerService $consumerService
     * @param string|null $name
     */
    public function __construct(
        ConsumerService $consumerService,
        string $name = null
    ) {
        $this->consumerService = $consumerService;
        parent::__construct($name);
    }

    protected function getCommandName(): string
    {
        return 'rabbitmq:consume-email';
    }

    protected function configure()
    {
        $this->setName($this->getCommandName())
            ->setDescription('Consume emails that are queued in RabbitMQ');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;
        $this->output->writeln('Started consuming emails from RabbitMQ');
        $this->consumerService->startListenQueue($this->output);

        return 0;
    }
}