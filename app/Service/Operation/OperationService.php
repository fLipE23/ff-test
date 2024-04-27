<?php

namespace App\Service\Operation;

use App\Domain\Account\Contract\Infrastructure\Repository\BalanceRepositoryContract;
use App\Domain\Account\Contract\Infrastructure\Repository\OperationRepositoryContract;
use App\Domain\Account\Contract\Infrastructure\Repository\TransactionManagerContract;
use App\Domain\Account\Contract\Service\OperationServiceContract;
use App\Domain\Account\DTO\CommonOperationData;
use App\Domain\Account\DTO\TransferOperationData;
use App\Domain\Account\Enum\OperationType;
use App\Domain\Account\Exception\NotEnoughMoneyException;


class OperationService implements OperationServiceContract
{

    public function __construct(
        private OperationRepositoryContract $operationRepository,
        private BalanceRepositoryContract $balanceRepository,
        private TransactionManagerContract $transactionManager,
    ) {
    }


    public function handleCredit(CommonOperationData $data): void
    {
        $amount = $data->getAmount();

        $this->operationRepository->insertOperation(
            $data->getUserId(),
            $amount->getValue(),
            $amount->getCurrency(),
            OperationType::TYPE_CREDIT->value,
            $data->getReason()
        );

        $this->balanceRepository->updateBalance(
            $data->getUserId(),
            $amount->getCurrency()
        );
    }


    public function handleDebit(CommonOperationData $data): void
    {
        try {
            $this->transactionManager->startTransaction(TransactionManagerContract::ISOLATION_LEVEL_REPEATABLE_READ);

            $amount = $data->getAmount();

            $balance = $this->balanceRepository->getUserBalanceForUpdate(
                $data->getUserId(),
                $data->getAmount()->getCurrency()
            );

            if ($balance->getAvailableAmount()->getValue() < $amount->getValue()) {
                throw new NotEnoughMoneyException();
            }

            $this->operationRepository->insertOperation(
                $data->getUserId(),
                $amount->getValue(),
                $amount->getCurrency(),
                OperationType::TYPE_DEBIT->value,
                $data->getReason()
            );

            $result = $this->balanceRepository->updateBalance(
                $data->getUserId(),
                $data->getAmount()->getCurrency(),
            );

            $this->transactionManager->commitTransaction();
        } catch (\Exception $e) {
            $this->transactionManager->rollbackTransaction();
            throw $e;
        }
    }

    public function handleTransfer(TransferOperationData $data): void
    {
        try {
            $this->transactionManager->startTransaction(TransactionManagerContract::ISOLATION_LEVEL_REPEATABLE_READ);

            $amount = $data->getAmount();

            $balance = $this->balanceRepository->getUserBalanceForUpdate(
                $data->getUserIdFrom(),
                $data->getAmount()->getCurrency()
            );

            if ($balance->getAvailableAmount()->getValue() < $amount->getValue()) {
                throw new NotEnoughMoneyException();
            }

            $this->operationRepository->insertOperation(
                $data->getUserIdFrom(),
                $amount->getValue(),
                $amount->getCurrency(),
                OperationType::TYPE_DEBIT->value,
                $data->getReason()
            );
            $this->operationRepository->insertOperation(
                $data->getUserIdTo(),
                $amount->getValue(),
                $amount->getCurrency(),
                OperationType::TYPE_CREDIT->value,
                $data->getReason()
            );

            $this->balanceRepository->updateBalance(
                $data->getUserIdFrom(),
                $data->getAmount()->getCurrency(),
            );
            $this->balanceRepository->updateBalance(
                $data->getUserIdTo(),
                $data->getAmount()->getCurrency(),
            );

            $this->transactionManager->commitTransaction();
        } catch (\Exception $e) {
            $this->transactionManager->rollbackTransaction();
            throw $e;
        }
    }


    public function handleBlock(CommonOperationData $data): void
    {
        try {
            $this->transactionManager->startTransaction(TransactionManagerContract::ISOLATION_LEVEL_REPEATABLE_READ);

            $amount = $data->getAmount();

            $balance = $this->balanceRepository->getUserBalanceForUpdate(
                $data->getUserId(),
                $data->getAmount()->getCurrency()
            );

            if ($balance->getAvailableAmount()->getValue() < $amount->getValue()) {
                throw new NotEnoughMoneyException();
            }

            $this->operationRepository->insertOperation(
                $data->getUserId(),
                $amount->getValue(),
                $amount->getCurrency(),
                OperationType::TYPE_BLOCK->value,
                $data->getReason()
            );

            $this->balanceRepository->updateBalance(
                $data->getUserId(),
                $data->getAmount()->getCurrency(),
            );

            $this->transactionManager->commitTransaction();
        } catch (\Exception $e) {
            $this->transactionManager->rollbackTransaction();
            throw $e;
        }
    }

    public function handleRelease(CommonOperationData $data): void
    {
        try {
            $this->transactionManager->startTransaction(TransactionManagerContract::ISOLATION_LEVEL_REPEATABLE_READ);

            $amount = $data->getAmount();

            $balance = $this->balanceRepository->getUserBalanceForUpdate(
                $data->getUserId(),
                $data->getAmount()->getCurrency()
            );

            if ($balance->getBlockedAmount()->getValue() < $amount->getValue()) {
                throw new NotEnoughMoneyException(); // not enough blocked money
            }

            $this->operationRepository->insertOperation(
                $data->getUserId(),
                $amount->getValue(),
                $amount->getCurrency(),
                OperationType::TYPE_RELEASE->value,
                $data->getReason()
            );

            $this->balanceRepository->updateBalance(
                $data->getUserId(),
                $data->getAmount()->getCurrency(),
            );

            $this->transactionManager->commitTransaction();
        } catch (\Exception $e) {
            $this->transactionManager->rollbackTransaction();
            throw $e;
        }
    }

    public function handleDebitBlocked(CommonOperationData $data): void
    {
        try {
            $this->transactionManager->startTransaction(TransactionManagerContract::ISOLATION_LEVEL_REPEATABLE_READ);

            $amount = $data->getAmount();

            $balance = $this->balanceRepository->getUserBalanceForUpdate(
                $data->getUserId(),
                $data->getAmount()->getCurrency()
            );

            if ($balance->getBlockedAmount()->getValue() < $amount->getValue()) {
                throw new NotEnoughMoneyException(); // not enough blocked money
            }

            $this->operationRepository->insertOperation(
                $data->getUserId(),
                $amount->getValue(),
                $amount->getCurrency(),
                OperationType::TYPE_RELEASE->value,
                $data->getReason()
            );

            $this->operationRepository->insertOperation(
                $data->getUserId(),
                $amount->getValue(),
                $amount->getCurrency(),
                OperationType::TYPE_DEBIT->value,
                $data->getReason()
            );

            $this->balanceRepository->updateBalance(
                $data->getUserId(),
                $data->getAmount()->getCurrency(),
            );

            $this->transactionManager->commitTransaction();
        } catch (\Exception $e) {
            $this->transactionManager->rollbackTransaction();
            throw $e;
        }
    }
}
