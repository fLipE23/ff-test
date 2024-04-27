<?php

namespace App\Domain\Account\Contract\Action;

use App\Domain\Account\DTO\OperationHandleResult;

interface DebitOperationHandlerContract
{
    public function handle(array $data): OperationHandleResult;

}
