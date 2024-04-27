<?php

namespace Tests\Unit;


use App\Domain\Account\Contract\Infrastructure\Repository\TransactionManagerContract;
use App\Domain\Account\DTO\CommonOperationData;
use App\Domain\Account\Enum\OperationType;
use App\Domain\Account\Exception\NotEnoughMoneyException;
use App\Domain\Account\Value\Amount;
use App\Service\Operation\OperationService;
use Tests\Fakes\FakeBalanceRepository;
use Tests\Fakes\FakeDatabase;
use Tests\Fakes\FakeOperationRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class OperationsServiceTest extends TestCase
{
    private OperationService $operationService;
    private FakeDatabase $fakeDb;
    private FakeOperationRepository $fakeOperationRepo;
    private FakeBalanceRepository $fakeBalanceRepo;
    private MockObject $transactionManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeDb = new FakeDatabase();
        $this->fakeOperationRepo = new FakeOperationRepository($this->fakeDb);
        $this->fakeBalanceRepo = new FakeBalanceRepository($this->fakeDb);
        $this->transactionManager = $this->createMock(TransactionManagerContract::class);

        $this->operationService = new OperationService(
            $this->fakeOperationRepo,
            $this->fakeBalanceRepo,
            $this->transactionManager
        );
    }

    public function testHandleCredit()
    {
        $this->transactionManager->expects($this->never())
            ->method('startTransaction');
        $this->transactionManager->expects($this->never())
            ->method('commitTransaction');

        $userId = 1;
        $amount = new Amount(100, 'USD');
        $data = new CommonOperationData($userId, $amount, OperationType::TYPE_CREDIT);

        $this->operationService->handleCredit($data);

        $balance = $this->fakeBalanceRepo->getUserBalanceForUpdate($userId, $amount->getCurrency());
        $this->assertEquals(100, $balance->amount);

        $operations = $this->fakeDb->select('operations', ['user_id' => $userId]);
        $this->assertCount(1, $operations);
        $this->assertEquals(OperationType::TYPE_CREDIT->value, $operations[0]['type']);
        $this->assertEquals(100, $operations[0]['amount']);
        $this->assertEquals('USD', $operations[0]['currency']);
    }


    public function testHandleBlockSuccess()
    {
        $this->transactionManager->expects($this->once())
            ->method('startTransaction');
        $this->transactionManager->expects($this->once())
            ->method('commitTransaction');
        $this->transactionManager->expects($this->never())
            ->method('rollbackTransaction');

        $userId = 1;
        $amount = new Amount(50, 'USD');
        $data = new CommonOperationData($userId, $amount, OperationType::TYPE_BLOCK);

        $this->fakeDb->insert('operations', [
            'user_id' => $userId,
            'amount' => 100,
            'currency' => 'USD',
            'type' => OperationType::TYPE_CREDIT->value,
        ]);
        $this->fakeBalanceRepo->updateBalance($userId, 'USD');

        $this->operationService->handleBlock($data);

        $updatedBalance = $this->fakeBalanceRepo->getUserBalanceForUpdate($userId, $amount->getCurrency());
        $this->assertEquals(50, $updatedBalance->getBlockedAmount()->getValue());
    }

    public function testHandleBlockFailure()
    {
        $this->transactionManager->expects($this->once())
            ->method('startTransaction');
        $this->transactionManager->expects($this->never())
            ->method('commitTransaction');
        $this->transactionManager->expects($this->once())
            ->method('rollbackTransaction');

        $userId = 1;
        $amount = new Amount(150, 'USD');
        $data = new CommonOperationData($userId, $amount, OperationType::TYPE_BLOCK);

        $this->fakeDb->insert('operations', [
            'user_id' => $userId,
            'amount' => 100,
            'currency' => 'USD',
            'type' => OperationType::TYPE_CREDIT->value,
        ]);
        $this->fakeBalanceRepo->updateBalance($userId, 'USD');

        $this->expectException(NotEnoughMoneyException::class);
        $this->operationService->handleBlock($data);
    }


    public function testHandleReleaseSuccess()
    {
        $this->transactionManager->expects($this->once())
            ->method('startTransaction');
        $this->transactionManager->expects($this->once())
            ->method('commitTransaction');
        $this->transactionManager->expects($this->never())
            ->method('rollbackTransaction');

        $userId = 1;
        $amount = new Amount(50, 'USD');
        $data = new CommonOperationData(
            $userId,
            $amount,
            OperationType::TYPE_RELEASE,
        );

        $this->fakeDb->insert('operations', [
            'user_id' => $userId,
            'amount' => 100,
            'currency' => 'USD',
            'type' => OperationType::TYPE_CREDIT->value,
        ]);
        $this->fakeDb->insert('operations', [
            'user_id' => $userId,
            'amount' => 50,
            'currency' => 'USD',
            'type' => OperationType::TYPE_BLOCK->value,
        ]);
        $this->fakeBalanceRepo->updateBalance($userId, 'USD');


        $this->operationService->handleRelease($data);

        $updatedBalance = $this->fakeBalanceRepo->getUserBalanceForUpdate($userId, $amount->getCurrency());
        $this->assertEquals(0, $updatedBalance->getBlockedAmount()->getValue());
    }


    public function testHandleDebitSuccess()
    {
        $this->transactionManager->expects($this->once())
            ->method('startTransaction');
        $this->transactionManager->expects($this->once())
            ->method('commitTransaction');
        $this->transactionManager->expects($this->never())
            ->method('rollbackTransaction');

        $userId = 1;
        $amount = new Amount(50, 'USD');
        $data = new CommonOperationData($userId, $amount, OperationType::TYPE_CREDIT, "Paying for services");

        $this->fakeDb->insert('operations', [
            'user_id' => $userId,
            'amount' => 100,
            'currency' => 'USD',
            'type' => OperationType::TYPE_CREDIT->value,
        ]);
        $this->fakeBalanceRepo->updateBalance($userId, 'USD');

        $this->operationService->handleDebit($data);

        $updatedBalance = $this->fakeBalanceRepo->getUserBalanceForUpdate($userId, $amount->getCurrency());
        $this->assertEquals(50, $updatedBalance->getAmount()->getValue());
    }


    public function testHandleDebitFailure()
    {
        $this->transactionManager->expects($this->once())
            ->method('startTransaction');
        $this->transactionManager->expects($this->never())
            ->method('commitTransaction');
        $this->transactionManager->expects($this->once())
            ->method('rollbackTransaction');

        $userId = 1;
        $amount = new Amount(150, 'USD');
        $data = new CommonOperationData($userId, $amount, OperationType::TYPE_DEBIT);

        $this->fakeDb->insert('operations', [
            'user_id' => $userId,
            'amount' => 100,
            'currency' => 'USD',
            'type' => OperationType::TYPE_CREDIT->value,
        ]);
        $this->fakeBalanceRepo->updateBalance($userId, 'USD');

        $this->expectException(NotEnoughMoneyException::class);

        $this->operationService->handleDebit($data);
    }


}
