<?php

namespace App\Actions;

use App\Domain\Account\Contract\Action\TransferOperationHandlerContract;
use App\Domain\Account\Contract\Service\OperationServiceContract;
use App\Domain\Account\DTO\OperationDataDTOFactory;
use App\Domain\Account\DTO\OperationHandleResult;
use App\Domain\Account\Exception\NotEnoughMoneyException;

class TransferHandler implements TransferOperationHandlerContract
{
    public function __construct(
        private OperationServiceContract $operationService,
    ) {
    }

    public function handle(array $data): OperationHandleResult
    {
        try {
            $this->operationService->handleTransfer(
                OperationDataDTOFactory::createTransfer($data)
            );
        } catch (NotEnoughMoneyException $e) {
            return new OperationHandleResult(
                false, $e->getMessage()
            );
        } catch (\Exception $e) {
            throw $e;
        }

        return new OperationHandleResult(true);
    }
}
