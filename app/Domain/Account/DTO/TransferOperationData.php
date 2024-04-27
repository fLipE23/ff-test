<?php

namespace App\Domain\Account\DTO;

use App\Domain\Account\Value\Amount;

class TransferOperationData
{
    public function __construct(
        private int $userIdFrom,
        private int $userIdTo,
        private Amount $amount,
        private ?string $reason = null,
    ) {
    }

    public function getUserIdFrom(): int
    {
        return $this->userIdFrom;
    }

    public function getUserIdTo(): int
    {
        return $this->userIdTo;
    }

    public function getAmount(): Amount
    {
        return $this->amount;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

}
