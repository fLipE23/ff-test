<?php

namespace App\Console\Commands;

use App\Domain\Account\Contract\Action\BlockOperationHandlerContract;
use App\Domain\Account\Contract\Action\DebitBlockedOperationHandlerContract;
use App\Domain\Account\Contract\Action\CreditOperationHandlerContract;
use App\Domain\Account\Contract\Action\DebitOperationHandlerContract;
use App\Domain\Account\Contract\Action\ReleaseOperationHandlerContract;
use App\Domain\Account\Contract\Action\TransferOperationHandlerContract;
use App\Domain\Account\Contract\Infrastructure\Queue\QueueServiceInterface;
use App\Domain\Account\Enum\OperationType;
use Illuminate\Console\Command;


class ConsumeMessages extends Command
{
    protected $signature = 'rmq:consume';
    protected $description = '';

    public function __construct(
        private QueueServiceInterface $queueService,
        private DebitOperationHandlerContract $debitOperationHandler,
        private CreditOperationHandlerContract $creditOperationHandler,
        private TransferOperationHandlerContract $transferOperationHandler,
        private BlockOperationHandlerContract $blockOperationHandler,
        private DebitBlockedOperationHandlerContract $debitBlockedHandler,
        private ReleaseOperationHandlerContract $releaseOperationHandler,
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info("Starting consumer...");

        $this->queueService->consume('operations', function ($message) {
            $this->info('Message received: '.$message->body);

            try {
                $data = json_decode($message->body, true);

                switch ($data['type']) {
                    case OperationType::TYPE_CREDIT->value:
                        $result = $this->creditOperationHandler->handle($data);
                        break;
                    case OperationType::TYPE_DEBIT->value:
                        $result = $this->debitOperationHandler->handle($data);
                        break;
                    case OperationType::TYPE_TRANSFER->value:
                        $result = $this->transferOperationHandler->handle($data);
                        break;
                    case OperationType::TYPE_BLOCK->value:
                        $result = $this->blockOperationHandler->handle($data);
                        break;
                    case OperationType::TYPE_DEBIT_BLOCKED->value:
                        $result = $this->debitBlockedHandler->handle($data);
                        break;
                    case OperationType::TYPE_RELEASE->value:
                        $result = $this->releaseOperationHandler->handle($data);
                        break;
                }

                $this->queueService->publish(
                    'operations_results',
                    json_encode($result)
                );

                $message->ack();
            } catch (\Exception $e) {
                $this->error('Handling message error: '.$e->getMessage());
                $message->nack();
            }
        });
    }
}
