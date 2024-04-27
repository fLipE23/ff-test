<?php

namespace App\Infrastructure\Queue;

use App\Domain\Account\Contract\Infrastructure\Queue\QueueServiceInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPIOException;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMqService implements QueueServiceInterface
{

    const CONNECTION_TRIES = 10;

    protected ?AMQPStreamConnection $connection = null;
    protected ?AMQPChannel $channel = null;

    public function __construct(
        private string $host,
        private int $port,
        private string $user,
        private string $password,
        private string $vhost
    ) {
    }


    public function consume(string $queue, callable $callback): void
    {
        $this->connect();
        $this->checkOrCreateQueue($queue);

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume(
            $queue, '', false, false, false, false, $callback
        );

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }


    public function publish(string $queue, string $message): void
    {
        $this->connect();
        $this->checkOrCreateQueue($queue);

        $amqpMessage = new AMQPMessage($message);

        $this->channel->basic_publish($amqpMessage, '', $queue);
    }

    public function close(): void
    {
        if (isset($this->channel)) {
            $this->channel->close();
        }
        if (isset($this->connection)) {
            $this->connection->close();
        }
    }

    public function __destruct()
    {
        $this->close();
    }


    private function connect(int $tries = 0)
    {
        if (isset($this->connection) && $this->connection->isConnected()) {
            return;
        }

        try {
            $this->connection = new AMQPStreamConnection(
                $this->host, $this->port, $this->user, $this->password, $this->vhost
            );
        } catch (AMQPIOException $e) {
            if ($tries < self::CONNECTION_TRIES) {
                $tries++;
                sleep(1);
                $this->connect($tries);
            }

            throw new \Exception('Failed to connect to RabbitMQ: '.$e->getMessage().' (tries: '.$tries.')');
        }

        if (isset($this->channel) && $this->channel->is_open()) {
            return;
        }
        $this->channel = $this->connection->channel();
    }


    private function checkOrCreateQueue(string $queue): void
    {
        $this->channel->queue_declare($queue, false, true, false, false);
    }


}
