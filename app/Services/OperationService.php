<?php

namespace App\Services;

use App\DTO\OperationCreateDTO;
use App\Jobs\ProcessOperationJob;
use App\Models\Operation;
use App\Models\User;

class OperationService
{
    /**
     * Создает операцию и отправляет в очередь на обработку
     */
    public function create(OperationCreateDTO $operationCreateDTO): Operation
    {
        $operation = Operation::create([
            'user_id' => $operationCreateDTO->userId,
            'type' => $operationCreateDTO->type,
            'amount' => $operationCreateDTO->amount,
            'description' => $operationCreateDTO->description,
            'status' => Operation::STATUS_PENDING,
        ]);

        // Все операции теперь обрабатываются только через очереди
        ProcessOperationJob::dispatch($operation->id);

        return $operation;
    }

    /**
     * Алиас для create() - все операции только через очереди
     */
    public function store(OperationCreateDTO $operationCreateDTO): Operation
    {
        return $this->create($operationCreateDTO);
    }

    /**
     * Обработка операции из очереди (вызывается Job'ом)
     */
    public function processOperation(Operation $operation): void
    {
        if (! $operation->isPending()) {
            return;
        }

        // Используем безопасную транзакцию с блокировками для обработки из очереди
        $transactionService = app(TransactionService::class);

        $transactionService->executeWithRetry(fn () => $transactionService->executeWithUserLock(
            $operation->user_id,
            function (User $user) use ($operation): void {
                // Перезагружаем операцию с блокировкой
                $operation = Operation::where('id', $operation->id)->lockForUpdate()->first();

                if (! $operation || ! $operation->isPending()) {
                    return;
                }

                $this->processOperationSafely($operation, $user);
            }
        ));
    }

    /**
     * Безопасная обработка операции с уже заблокированным пользователем
     */
    private function processOperationSafely(Operation $operation, User $user): void
    {
        $balance = $user->ensureBalance();

        if ($operation->isDeposit()) {
            $balance->addAmount($operation->amount);
        } elseif ($operation->isWithdrawal()) {
            // Атомарное списание: обновление произойдет только если средств достаточно
            if (! $balance->safeSubtract($operation->amount)) {
                throw new \Exception('Insufficient balance. Current: '.$balance->amount.', Required: '.$operation->amount);
            }
        }

        $operation->markAsCompleted();
    }

    /**
     * Общий метод для получения операций с параметром limit
     * Если limit не задан - возвращает операции с пагинацией по 10 элементов
     * Если limit задан - возвращает указанное количество последних операций без пагинации
     */
    public function getOperations(int $userId, ?int $limit = null, ?string $search = null, int $page = 1, string $sortDirection = 'desc'): array
    {
        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        $query = Operation::forUser($userId)->orderBy('created_at', $sortDirection);

        if ($search) {
            $query->searchByDescription($search);
        }

        if ($limit !== null) {
            // Если limit задан - возвращаем указанное количество операций без пагинации
            $operations = $query->limit($limit)->get();

            return [
                'data' => $operations,
                'pagination' => null,
            ];
        } else {
            // Если limit не задан - используем пагинацию по 10 операций
            $perPage = 10;
            $paginated = $query->paginate($perPage, ['*'], 'page', $page);

            return [
                'data' => $paginated->items(),
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                    'from' => $paginated->firstItem(),
                    'to' => $paginated->lastItem(),
                ],
            ];
        }
    }
}
