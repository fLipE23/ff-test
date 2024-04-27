<?php

namespace App\Actions;

use App\Domain\Account\Contract\Action\DebitBlockedOperationHandlerContract;
use App\Domain\Account\Contract\Service\OperationServiceContract;
use App\Domain\Account\DTO\OperationDataDTOFactory;
use App\Domain\Account\DTO\OperationHandleResult;
use App\Domain\Account\Exception\NotEnoughMoneyException;

class DebitBlockedHandler implements DebitBlockedOperationHandlerContract
{
    public function __construct(
        private OperationServiceContract $operationService,
    ) {
    }

    public function handle(array $data): OperationHandleResult
    {
        try {
            $this->operationService->handleDebitBlocked(
                OperationDataDTOFactory::createDebitBlocked($data)
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
