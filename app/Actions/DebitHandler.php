<?php

namespace App\Actions;

use App\Domain\Account\Contract\Action\DebitBlockedOperationHandlerContract;
use App\Domain\Account\Contract\Action\DebitOperationHandlerContract;
use App\Domain\Account\Contract\Service\OperationServiceContract;
use App\Domain\Account\DTO\OperationDataDTOFactory;
use App\Domain\Account\DTO\OperationHandleResult;
use App\Domain\Account\Exception\NotEnoughMoneyException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class DebitHandler implements DebitOperationHandlerContract
{
    public function __construct(
        private OperationServiceContract $operationService,
    ) {
    }

    public function handle(array $data): OperationHandleResult
    {
        try {
            $this->operationService->handleDebit(
                OperationDataDTOFactory::createDebit($data)
            );
        } catch (NotEnoughMoneyException $e) {
            Log::info($e->getMessage());

            return new OperationHandleResult(
                false, $e->getMessage()
            );
        } catch (QueryException $e) {
            // for tests
            if (str_contains($e->getMessage(), 'concurrent update')) {
                sleep(1);
                Log::info('Retrying operation');
                return $this->handle($data);
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return new OperationHandleResult(true);
    }
}
