<?php

namespace App\Domain\Account\Contract\Infrastructure\Repository;

use App\Models\Balance;

interface BalanceRepositoryContract
{
    public function getUserBalanceForUpdate(int $userId, string $currency): Balance;

    public function updateBalance(int $user_id, string $currency): bool;

}

