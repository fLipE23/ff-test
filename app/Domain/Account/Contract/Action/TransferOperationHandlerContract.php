<?php

namespace App\Domain\Account\Contract\Action;

use App\Domain\Account\DTO\OperationHandleResult;

interface TransferOperationHandlerContract
{
    public function handle(array $data): OperationHandleResult;

}
