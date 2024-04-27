<?php

namespace App\Domain\Account\Contract\Infrastructure\Repository;


interface OperationRepositoryContract
{

    public function insertOperation(
        int $userId,
        int $amount,
        string $currency,
        string $type,
        string $reason = null
    ): int;

}
