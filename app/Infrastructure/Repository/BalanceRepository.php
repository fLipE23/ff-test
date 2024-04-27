<?php

namespace App\Infrastructure\Repository;

use App\Domain\Account\Contract\Infrastructure\Repository\BalanceRepositoryContract;
use App\Domain\Account\Enum\OperationType;
use App\Models\Balance;
use Illuminate\Support\Facades\DB;

class BalanceRepository implements BalanceRepositoryContract
{

    public function __construct(
        private Balance $model
    ) {
    }

    public function getUserBalanceForUpdate(int $userId, string $currency): Balance
    {
        $balance = $this->model
            ->where('user_id', $userId)
            ->where('currency', $currency)
            ->lockForUpdate()
            ->first();

        if (is_null($balance)) {
            $balance = new Balance([
                'user_id' => $userId,
                'amount' => 0,
                'blocked_amount' => 0,
                'currency' => $currency,
            ]);
        }

        return $balance;
    }

    public function updateBalance(int $user_id, string $currency): bool
    {
        $typeDebit = OperationType::TYPE_DEBIT->value;
        $typeCredit = OperationType::TYPE_CREDIT->value;
        $typeBlock = OperationType::TYPE_BLOCK->value;
        $typeRelease = OperationType::TYPE_RELEASE->value;

        return DB::statement("
            insert into balances (
                user_id, amount, blocked_amount, currency
            )
                (select user_id,
                        coalesce(SUM(
                                         CASE
                                             WHEN type = '{$typeDebit}' THEN -amount
                                             WHEN type = '{$typeCredit}' THEN amount
                                             ELSE 0
                                             END
                                 ), 0) as amount,
                        coalesce(SUM(
                                         CASE
                                             WHEN type = '{$typeBlock}' THEN amount
                                             WHEN type = '{$typeRelease}' THEN -amount
                                             ELSE 0
                                             END
                                 ), 0) as blocked_amount,
                        currency
                from operations
                where user_id = :user_id
                   and currency = :currency
                group by (user_id, currency)
                )
            on conflict (user_id, currency) DO UPDATE
                SET
                    amount = EXCLUDED.amount,
                    blocked_amount = EXCLUDED.blocked_amount
            returning id, user_id, amount, blocked_amount, currency;
        ", [
            'user_id' => $user_id,
            'currency' => $currency,
        ]);
    }

}

