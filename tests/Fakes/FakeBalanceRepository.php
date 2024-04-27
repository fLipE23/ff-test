<?php

namespace Tests\Fakes;


use App\Domain\Account\Contract\Infrastructure\Repository\BalanceRepositoryContract;
use App\Domain\Account\Enum\OperationType;
use App\Models\Balance;

class FakeBalanceRepository implements BalanceRepositoryContract
{
    private FakeDatabase $db;

    public function __construct(FakeDatabase $db)
    {
        $this->db = $db;
    }

    public function getUserBalanceForUpdate(int $userId, string $currency): Balance
    {
        $results = $this->db->select('balances', ['user_id' => $userId, 'currency' => $currency]);
        $balanceData = reset($results);

        if (!$balanceData) {
            $this->db->insert('balances', [
                'user_id' => $userId,
                'amount' => 0,
                'blocked_amount' => 0,
                'currency' => $currency
            ]);
            $balanceData = [
                'user_id' => $userId,
                'amount' => 0,
                'blocked_amount' => 0,
                'currency' => $currency
            ];
        }

        $balance = new Balance();
        $balance->user_id = $balanceData['user_id'];
        $balance->amount = $balanceData['amount'];
        $balance->blocked_amount = $balanceData['blocked_amount'];
        $balance->currency = $balanceData['currency'];

        return $balance;
    }


    public function updateBalance(int $userId, string $currency): bool
    {
        $operations = $this->db->select('operations', ['user_id' => $userId, 'currency' => $currency]);
        $totalAmount = 0;
        $blockedAmount = 0;

        foreach ($operations as $operation) {
            switch ($operation['type']) {
                case OperationType::TYPE_CREDIT->value:
                    $totalAmount += $operation['amount'];
                    break;
                case OperationType::TYPE_DEBIT->value:
                    $totalAmount -= $operation['amount'];
                    break;
                case OperationType::TYPE_BLOCK->value:
                    $blockedAmount += $operation['amount'];
                    break;
                case OperationType::TYPE_RELEASE->value:
                    $blockedAmount -= $operation['amount'];
                    break;
            }
        }

        $existingBalances = $this->db->select('balances', ['user_id' => $userId, 'currency' => $currency]);
        if (count($existingBalances) > 0) {
            // Обновление существующего баланса
            $this->db->update('balances', ['user_id' => $userId, 'currency' => $currency], [
                'amount' => $totalAmount,
                'blocked_amount' => $blockedAmount
            ]);
        } else {
            // Вставка новой записи баланса, если не существует
            $this->db->insert('balances', [
                'user_id' => $userId,
                'currency' => $currency,
                'amount' => $totalAmount,
                'blocked_amount' => $blockedAmount
            ]);
        }

        return true;
    }


}
