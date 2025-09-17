<?php

namespace App\Services;

use App\DTO\OperationCreateDTO;
use App\DTO\OperationDTO;
use App\Models\Balance;
use App\Models\Operation;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OperationService
{
    public function create(OperationCreateDTO $dto): OperationDTO
    {
        return DB::transaction(function () use ($dto) {
            $balance = Balance::where('user_id', $dto->userId)->lockForUpdate()->first();
            if (! $balance) {
                throw new RuntimeException('Баланс пользователя не найден');
            }
            if ($dto->type === 'debit' && $balance->amount < $dto->amount) {
                throw new RuntimeException('Недостаточно средств');
            }
            $balance->amount = $dto->type === 'credit'
                ? $balance->amount + $dto->amount
                : $balance->amount - $dto->amount;
            $balance->save();
            $operation = Operation::create([
                'user_id' => $dto->userId,
                'type' => $dto->type,
                'amount' => $dto->amount,
                'description' => $dto->description,
            ]);

            return new OperationDTO(
                $operation->id,
                $operation->user_id,
                $operation->type,
                $operation->amount,
                $operation->description,
                $operation->created_at
            );
        });
    }
}
