<?php

namespace App\Infrastructure\Repository;

use App\Domain\Account\Contract\Infrastructure\Repository\TransactionManagerContract;
use Illuminate\Support\Facades\DB;

class EloquentTransactionManager implements TransactionManagerContract
{

    public function startTransaction($isolationLevel = TransactionManagerContract::ISOLATION_LEVEL_SERIALIZABLE): void
    {
        DB::statement("START TRANSACTION ISOLATION LEVEL $isolationLevel;");
        DB::statement("SET LOCAL lock_timeout = '5s';");
        DB::statement("SET LOCAL statement_timeout = '5s';");
    }

    public function commitTransaction(): void
    {
        DB::statement('COMMIT;');
    }

    public function rollbackTransaction(): void
    {
        DB::statement('ROLLBACK;');
    }


}

