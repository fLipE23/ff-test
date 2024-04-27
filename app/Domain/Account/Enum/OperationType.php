<?php

namespace App\Domain\Account\Enum;

enum OperationType: string
{

    case TYPE_CREDIT = 'credit';
    case TYPE_DEBIT = 'debit';
    case TYPE_BLOCK = 'block';
    case TYPE_RELEASE = 'release';
    case TYPE_DEBIT_BLOCKED = 'debit_blocked';
    case TYPE_TRANSFER = 'transfer';


}
