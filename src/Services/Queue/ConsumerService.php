<?php

namespace App\Services\Queue;

use App\Models\UserStockRequest;
use PhpAmqpLib\Channel\AMQPChannel;
use Symfony\Component\Console\Output\OutputInterface;
use App\Services\StockService;
use App\Services\EmailService;

class ConsumerService
{
    protected AMQPChannel $channel;
    protected StockService $stockService;
    protected EmailService $emailService;

    /**
     * @param AMQPChannel $channel
     * @param StockService $stockService
     */
    public function __construct(
        AMQPChannel $channel,
        StockService $stockService,
        EmailService $emailService
    ) {
        $this->channel = $channel;
        $this->stockService = $stockService;
        $this->emailService = $emailService;
    }

    /**
     * @param OutputInterface|null $output
     * @return void
     */
    public function startListenQueue($output)
    {
        $this->channel->queue_declare('email', false, true, false, false);
        $this->channel->basic_qos(null, 1, null);

        $this->channel->basic_consume(
            'email',
            '',
            false,
            false,
            false,
            false,
            function ($message) use ($output) {
                try {
                    $this->logToOutput($output, 'Consuming message...');
                    $this->logToOutput($output, $message->body);

                    $decodedMessage = json_decode($message->body, true);

                    $userStockRequestId = $decodedMessage['users_stock_request_id'] ?? null;
                    $stockRequest = null;

                    if (!empty($userStockRequestId)) {
                        $stockRequest = UserStockRequest::find($userStockRequestId);
                    }

                    if ($stockRequest) {
                        $user = $stockRequest->user;
                        $stockInformation = $this->stockService->normalizeStockResponse($stockRequest->getResponse());

                        if ($user && $user->email && !empty($stockInformation)) {
                            $this->emailService->sendStockInformationEmail([$user->email => $user->name], $stockInformation);
                        }
                    }
                } catch (\Exception $e) {
                }

                /** @var AMQPChannel $channel */
                $channel = $message->delivery_info['channel'];
                $channel->basic_ack($message->delivery_info['delivery_tag']);
            }
        );

        while (count($this->channel->callbacks)) {
            try {
                $this->channel->wait();
            } catch (\ErrorException $e) {
                $this->logToOutput($output, 'Error: ' . $e->getMessage());
            }
        }

        $this->channel->close();
    }

    protected function logToOutput($output, $message): void
    {
        if ($output instanceof OutputInterface) {
            $output->writeln($message);
        }
    }
}