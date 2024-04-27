<?php

namespace App\Infrastructure\Repository;


use App\Domain\Account\Contract\Infrastructure\Repository\OperationRepositoryContract;
use App\Models\Operation;

class OperationRepository implements OperationRepositoryContract
{

    public function __construct(
        private Operation $model
    ) {
    }

    public function insertOperation(
        int $userId,
        int $amount,
        string $currency,
        string $type,
        string $reason = null
    ): int {
        return $this->model->insertGetId(
            [
                'user_id' => $userId,
                'amount' => $amount,
                'currency' => $currency,
                'type' => $type,
                'reason' => $reason,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

}
