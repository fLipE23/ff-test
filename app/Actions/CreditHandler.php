<?php

namespace App\Actions;

use App\Domain\Account\Contract\Action\CreditOperationHandlerContract;
use App\Domain\Account\Contract\Service\OperationServiceContract;
use App\Domain\Account\DTO\OperationDataDTOFactory;
use App\Domain\Account\DTO\OperationHandleResult;
use App\Domain\Account\Exception\NotEnoughMoneyException;

class CreditHandler implements CreditOperationHandlerContract
{
    public function __construct(
        private OperationServiceContract $operationService,
    ) {
    }

    public function handle(array $data): OperationHandleResult
    {
        try {
            $this->operationService->handleCredit(
                OperationDataDTOFactory::createCredit($data)
            );
        } catch (NotEnoughMoneyException $e) {
            return new OperationHandleResult(
                false, $e->getMessage()
            );
        } catch (\Exception $e) {
            return new OperationHandleResult(
                false, $e->getMessage()
            );
        }

        return new OperationHandleResult(true);
    }
}
