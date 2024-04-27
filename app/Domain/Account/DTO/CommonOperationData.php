<?php

namespace App\Domain\Account\DTO;

use App\Domain\Account\Enum\OperationType;
use App\Domain\Account\Value\Amount;

class CommonOperationData
{

    // common data - for debit / credit / block / release / credit blocked
    public function __construct(
        private int $userId,
        private Amount $amount,
        private OperationType $type,
        private ?string $reason = null
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getAmount(): Amount
    {
        return $this->amount;
    }

    public function getType(): OperationType
    {
        return $this->type;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

}
