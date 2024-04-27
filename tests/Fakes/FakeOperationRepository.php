<?php

namespace Tests\Fakes;


use App\Domain\Account\Contract\Infrastructure\Repository\OperationRepositoryContract;

class FakeOperationRepository implements OperationRepositoryContract
{
    private FakeDatabase $db;

    public function __construct(FakeDatabase $db)
    {
        $this->db = $db;
    }

    public function insertOperation(
        int $userId,
        int $amount,
        string $currency,
        string $type,
        string $reason = null
    ): int {
        return $this->db->insert('operations', [
            'user_id' => $userId,
            'amount' => $amount,
            'currency' => $currency,
            'type' => $type,
            'reason' => $reason,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }


}



