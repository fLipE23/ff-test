<?php

namespace App\Domain\Account\Contract\Service;

use App\Domain\Account\DTO\CommonOperationData;
use App\Domain\Account\DTO\TransferOperationData;


interface OperationServiceContract
{

    public function handleCredit(CommonOperationData $data): void;

    public function handleDebit(CommonOperationData $data): void;

    public function handleTransfer(TransferOperationData $data): void;

    public function handleBlock(CommonOperationData $data): void;

    public function handleRelease(CommonOperationData $data): void;

    public function handleDebitBlocked(CommonOperationData $data): void;


}
