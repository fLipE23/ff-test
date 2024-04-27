<?php

namespace App\Domain\Account\Contract\Action;

use App\Domain\Account\DTO\OperationHandleResult;

interface BlockOperationHandlerContract
{
    public function handle(array $data): OperationHandleResult;

}
