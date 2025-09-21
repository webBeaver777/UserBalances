<?php

namespace App\Services;

use App\DTO\OperationCreateDTO;
use App\Jobs\ProcessOperationJob;
use App\Models\Balance;
use App\Models\Operation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OperationService
{
    public function create(OperationCreateDTO $operationCreateDTO): void
    {
        ProcessOperationJob::dispatch($operationCreateDTO);
    }

    public function store(OperationCreateDTO $operationCreateDTO): Operation
    {
        return DB::transaction(function () use ($operationCreateDTO) {
            $balance = Balance::where('user_id', $operationCreateDTO->userId)->lockForUpdate()->first();
            if ($operationCreateDTO->type === 'debit') {
                if ($balance->amount < $operationCreateDTO->amount) {
                    // Операция неудачна, фиксируем причину
                    return Operation::create([
                        'user_id' => $operationCreateDTO->userId,
                        'type' => $operationCreateDTO->type,
                        'amount' => $operationCreateDTO->amount,
                        'description' => $operationCreateDTO->description,
                        'status' => 'failed',
                        'fail_reason' => 'Недостаточно средств на балансе',
                    ]);
                }
                $balance->amount -= $operationCreateDTO->amount;
            } else {
                $balance->amount += $operationCreateDTO->amount;
            }
            $balance->save();

            // Операция успешна
            return Operation::create([
                'user_id' => $operationCreateDTO->userId,
                'type' => $operationCreateDTO->type,
                'amount' => $operationCreateDTO->amount,
                'description' => $operationCreateDTO->description,
                'status' => 'success',
                'fail_reason' => null,
            ]);
        });
    }

    public function getRecentOperations(int $userId, int $limit = 5): Collection
    {
        return Operation::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function searchOperations(int $userId, ?string $description = null): Collection
    {
        $query = Operation::where('user_id', $userId);
        if ($description) {
            $query->where('description', 'like', '%'.$description.'%');
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function operateByEmail(string $email, string $type, float $amount, string $description): Operation
    {
        $user = User::where('email', $email)->first();
        if (! $user) {
            throw new ModelNotFoundException("Пользователь с email '{$email}' не найден");
        }
        $balance = Balance::where('user_id', $user->id)->lockForUpdate()->first();
        if (! $balance) {
            throw new ModelNotFoundException('Баланс пользователя не найден');
        }
        if ($type === 'debit' && $balance->amount < $amount) {
            throw ValidationException::withMessages([
                'amount' => 'На балансе недостаточно средств для выполнения операции.',
            ]);
        }
        $operationCreateDTO = new OperationCreateDTO(
            userId: $user->id,
            type: $type,
            amount: $amount,
            description: $description
        );

        return $this->store($operationCreateDTO);
    }

    public function getOperationsWithPagination(int $userId, ?string $search = null, bool $sortDesc = true, int $page = 1, int $perPage = 25): array
    {
        $query = Operation::where('user_id', $userId);

        // Добавляем поиск по описанию
        if ($search) {
            $query->where('description', 'like', '%'.$search.'%');
        }

        // Добавляем сортировку по дате
        if ($sortDesc) {
            $query->orderByDesc('created_at');
        } else {
            $query->orderBy('created_at');
        }

        // Получаем общее количество записей
        $total = $query->count();

        // Применяем пагинацию
        $operations = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return [
            'data' => $operations,
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage),
            'from' => $total > 0 ? ($page - 1) * $perPage + 1 : 0,
            'to' => min($page * $perPage, $total),
        ];
    }

    public function getOperationsWithLimit(int $userId, ?string $search = null, bool $sortDesc = true, int $limit = 5): array
    {
        $query = Operation::where('user_id', $userId);

        // Добавляем поиск по описанию
        if ($search) {
            $query->where('description', 'like', '%'.$search.'%');
        }

        // Добавляем сортировку по дате
        if ($sortDesc) {
            $query->orderByDesc('created_at');
        } else {
            $query->orderBy('created_at');
        }

        $operations = $query->limit($limit)->get();

        return $operations->toArray();
    }
}
