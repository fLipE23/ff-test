<?php

namespace App\Providers;

use App\Actions\BlockHandler;
use App\Actions\DebitBlockedHandler;
use App\Actions\CreditHandler;
use App\Actions\DebitHandler;
use App\Actions\ReleaseHandler;
use App\Actions\TransferHandler;
use App\Domain\Account\Contract\Action\BlockOperationHandlerContract;
use App\Domain\Account\Contract\Action\DebitBlockedOperationHandlerContract;
use App\Domain\Account\Contract\Action\CreditOperationHandlerContract;
use App\Domain\Account\Contract\Action\DebitOperationHandlerContract;
use App\Domain\Account\Contract\Action\ReleaseOperationHandlerContract;
use App\Domain\Account\Contract\Action\TransferOperationHandlerContract;
use App\Domain\Account\Contract\Infrastructure\Queue\QueueServiceInterface;
use App\Domain\Account\Contract\Infrastructure\Repository\BalanceRepositoryContract;
use App\Domain\Account\Contract\Infrastructure\Repository\OperationRepositoryContract;
use App\Domain\Account\Contract\Infrastructure\Repository\TransactionManagerContract;
use App\Domain\Account\Contract\Service\OperationServiceContract;
use App\Infrastructure\Queue\RabbitMqService;
use App\Infrastructure\Repository\BalanceRepository;
use App\Infrastructure\Repository\EloquentTransactionManager;
use App\Infrastructure\Repository\OperationRepository;
use App\Service\Operation\OperationService;
use Illuminate\Support\ServiceProvider;

class AccountServiceProvider extends ServiceProvider
{

    public array $bindings = [
        // service
        OperationServiceContract::class => OperationService::class,

        // operation handlers
        DebitOperationHandlerContract::class => DebitHandler::class,
        CreditOperationHandlerContract::class => CreditHandler::class,
        TransferOperationHandlerContract::class => TransferHandler::class,
        BlockOperationHandlerContract::class => BlockHandler::class,
        DebitBlockedOperationHandlerContract::class => DebitBlockedHandler::class,
        ReleaseOperationHandlerContract::class => ReleaseHandler::class,

        // repositories
        OperationRepositoryContract::class => OperationRepository::class,
        BalanceRepositoryContract::class => BalanceRepository::class,
        TransactionManagerContract::class => EloquentTransactionManager::class,

    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(QueueServiceInterface::class, function () {
            $config = config('queue.connections.rabbitmq');
            return new RabbitMqService(
                $config['host'],
                $config['port'],
                $config['user'],
                $config['password'],
                $config['vhost'],
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}
