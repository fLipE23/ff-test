<?php

namespace App\Domain\Account\Contract\Infrastructure\Queue;


interface QueueServiceInterface
{
    public function consume(string $queue, callable $callback): void;

    public function publish(string $queue, string $message): void;

    public function close(): void;

}
