<?php

namespace App\Services;

use App\DTO\BalanceDTO;
use App\Models\Balance;
use RuntimeException;

class BalanceService
{
    public function getBalance(int $userId): BalanceDTO
    {
        $balance = Balance::where('user_id', $userId)->first();
        if (! $balance) {
            throw new RuntimeException('Баланс пользователя не найден');
        }

        return new BalanceDTO($userId, $balance->amount);
    }
}
