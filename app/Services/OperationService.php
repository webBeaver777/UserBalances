<?php

namespace App\Services;

use App\DTO\OperationCreateDTO;
use App\Jobs\ProcessOperationJob;
use App\Models\Balance;
use App\Models\Operation;
use Illuminate\Support\Facades\DB;

class OperationService
{
    public function create(OperationCreateDTO $dto): void
    {
        ProcessOperationJob::dispatch($dto);
    }

    public function store(OperationCreateDTO $dto): Operation
    {
        return DB::transaction(function () use ($dto) {
            $operation = Operation::create([
                'user_id' => $dto->userId,
                'type' => $dto->type,
                'amount' => $dto->amount,
                'description' => $dto->description,
            ]);

            $balance = Balance::where('user_id', $dto->userId)->lockForUpdate()->first();
            if ($dto->type === 'debit') {
                $balance->amount -= $dto->amount;
            } else {
                $balance->amount += $dto->amount;
            }
            $balance->save();

            return $operation;
        });
    }
}
