<?php

namespace App\Domain\Account\Contract\Infrastructure\Repository;


interface TransactionManagerContract
{

    const
        ISOLATION_LEVEL_READ_UNCOMMITTED = 'READ UNCOMMITTED',
        ISOLATION_LEVEL_READ_COMMITTED = 'READ COMMITTED',
        ISOLATION_LEVEL_REPEATABLE_READ = 'REPEATABLE READ',
        ISOLATION_LEVEL_SERIALIZABLE = 'SERIALIZABLE';

    public function startTransaction(): void;

    public function commitTransaction(): void;

    public function rollbackTransaction(): void;


}


