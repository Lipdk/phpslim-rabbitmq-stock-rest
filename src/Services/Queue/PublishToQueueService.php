<?php
declare(strict_types=1);

namespace App\Services\Queue;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class PublishToQueueService
{
    protected AMQPChannel $channel;

    /**
     * @param AMQPChannel $channel
     */
    public function __construct(AMQPChannel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * Publish message to RabbitMQ
     * @param int $userStockLogId
     * @return void
     */
    public function publish(int $userStockLogId)
    {
        $this->channel->queue_declare('email', false, true, false, false);

        $amqpMessage = new AMQPMessage(
            json_encode(['users_stock_request_id' => $userStockLogId]),
            array('delivery_mode' => 2)
        );

        $this->channel->basic_publish($amqpMessage, '', 'email');
        $this->channel->close();
    }
}