<?php

namespace App\Domain\Account\DTO;

use App\Domain\Account\Enum\OperationType;
use App\Domain\Account\Value\Amount;

class OperationDataDTOFactory
{

    public static function createDebit(array $data): CommonOperationData
    {
        return new CommonOperationData(
            $data['user_id'],
            new Amount($data['amount'], $data['currency']),
            OperationType::TYPE_CREDIT
        );
    }

    public static function createCredit(array $data): CommonOperationData
    {
        return new CommonOperationData(
            $data['user_id'],
            new Amount($data['amount'], $data['currency']),
            OperationType::TYPE_DEBIT
        );
    }

    public static function createBlock(array $data): CommonOperationData
    {
        return new CommonOperationData(
            $data['user_id'],
            new Amount($data['amount'], $data['currency']),
            OperationType::TYPE_BLOCK
        );
    }

    public static function createRelease(array $data): CommonOperationData
    {
        return new CommonOperationData(
            $data['user_id'],
            new Amount($data['amount'], $data['currency']),
            OperationType::TYPE_RELEASE
        );
    }

    public static function createDebitBlocked(array $data): CommonOperationData
    {
        return new CommonOperationData(
            $data['user_id'],
            new Amount($data['amount'], $data['currency']),
            OperationType::TYPE_DEBIT_BLOCKED
        );
    }

    public static function createTransfer(array $data): TransferOperationData
    {
        return new TransferOperationData(
            $data['user_id_from'],
            $data['user_id_to'],
            new Amount($data['amount'], $data['currency'])
        );
    }

}


