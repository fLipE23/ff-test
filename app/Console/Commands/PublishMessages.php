<?php

namespace App\Console\Commands;

use App\Domain\Account\Contract\Infrastructure\Queue\QueueServiceInterface;
use Illuminate\Console\Command;

class PublishMessages extends Command
{
    protected $signature = 'rmq:publish';

    protected $description = '';

    private QueueServiceInterface $queueService;

    public function __construct(QueueServiceInterface $queueService)
    {
        parent::__construct();
        $this->queueService = $queueService;
    }

    public function handle()
    {
        $queueName = 'operations';

        $messages = $this->generateMessage();

        foreach ($messages as $message) {
            $this->queueService->publish($queueName, $message);
            $this->info("Message published: ".$message);
        }
    }

    private function generateMessage()
    {
        $operations = [];
        $balances = [
            1 => 0,
            2 => 0,
            3 => 0,
        ];

        $types = ['credit', 'debit',];
        $currency = 'USD';

        // do not change the number of messages, because the order of execution is not guaranteed
        for ($i = 0; $i < 5; $i++) {
            $type = $types[array_rand($types)];
            $userId = rand(1, 2);
            $amount = rand(100, 1000) * 1000000;

            switch ($type) {
                case 'credit':
                    $operations[] = json_encode([
                        'user_id' => $userId,
                        'type' => $type,
                        'amount' => $amount,
                        'currency' => $currency,
                    ]);
                    $balances[$userId] += $amount;
                    break;
                case 'debit':
                    $operations[] = json_encode([
                        'user_id' => $userId,
                        'type' => $type,
                        'amount' => $amount,
                        'currency' => $currency,
                    ]);

                    if ($balances[$userId] - $amount > 0) {
                        $balances[$userId] -= $amount;
                    }

                    break;
            }
        }

        $this->info("Expecting balances:");

        foreach ($balances as $userId => $balance) {
            $this->info("User $userId: $balance $currency");
        }

        return $operations;
    }

}
