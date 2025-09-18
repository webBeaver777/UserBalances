<?php

namespace App\Services;

use App\DTO\OperationCreateDTO;
use App\Jobs\ProcessOperationJob;
use App\Models\Balance;
use App\Models\Operation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OperationService
{
    public function create(OperationCreateDTO $operationCreateDTO): void
    {
        ProcessOperationJob::dispatch($operationCreateDTO);
    }

    public function store(OperationCreateDTO $operationCreateDTO): Operation
    {
        return DB::transaction(function () use ($operationCreateDTO) {
            $operation = Operation::create([
                'user_id' => $operationCreateDTO->userId,
                'type' => $operationCreateDTO->type,
                'amount' => $operationCreateDTO->amount,
                'description' => $operationCreateDTO->description,
            ]);

            $balance = Balance::where('user_id', $operationCreateDTO->userId)->lockForUpdate()->first();
            if ($operationCreateDTO->type === 'debit') {
                $balance->amount -= $operationCreateDTO->amount;
            } else {
                $balance->amount += $operationCreateDTO->amount;
            }
            $balance->save();

            return $operation;
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
}
