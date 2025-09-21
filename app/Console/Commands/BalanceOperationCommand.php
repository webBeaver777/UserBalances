<?php

namespace App\Console\Commands;

use App\DTO\OperationCreateDTO;
use App\Services\OperationService;
use App\Services\UserService;
use Illuminate\Console\Command;

class BalanceOperationCommand extends Command
{
    protected $signature = 'balance:operation {email} {type} {amount} {description} {--async : Process operation asynchronously}';

    protected $description = 'Провести операцию с балансом пользователя';

    public function __construct(
        private readonly OperationService $operationService,
        private readonly UserService $userService
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $user = $this->userService->findByEmail($this->argument('email'));
        if (!$user instanceof \App\Models\User) {
            $this->error('Пользователь не найден');
            return;
        }

        $type = $this->argument('type');
        $amount = (float) $this->argument('amount');
        $description = $this->argument('description');

        // Валидация типа операции
        if (! in_array($type, ['deposit', 'withdrawal'])) {
            $this->error('Недопустимый тип операции. Используйте: deposit или withdrawal');
            return;
        }

        // Валидация суммы
        if ($amount <= 0) {
            $this->error('Сумма должна быть больше нуля');
            return;
        }

        $operationCreateDTO = new OperationCreateDTO(
            $user->id,
            $type,
            $amount,
            $description
        );

        try {
            if ($this->option('async')) {
                // Асинхронная обработка
                $this->operationService->create($operationCreateDTO);
                $this->info("Операция поставлена в очередь: {$type} {$amount} для пользователя {$user->email}");
            } else {
                // Синхронная обработка
                $operation = $this->operationService->store($operationCreateDTO);
                $this->info("Операция выполнена: {$operation->type} {$operation->amount} для пользователя {$user->email}");
                $this->info("Статус операции: {$operation->status}");

                // Показываем новый баланс
                $user->refresh();
                $balance = $user->getBalanceAmount();
                $this->info("Текущий баланс пользователя: {$balance} ₽");
            }
        } catch (\Exception $e) {
            $this->error('Ошибка при выполнении операции: '.$e->getMessage());
        }
    }
}
