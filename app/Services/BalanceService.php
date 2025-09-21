<?php

namespace App\Services;

use App\DTO\BalanceDTO;
use App\Models\Balance;
use App\Models\User;

class BalanceService
{
    public function getBalance(int $userId): BalanceDTO
    {
        $user = User::with('balance')->findOrFail($userId);
        $balance = $user->balance ?? $user->ensureBalance();

        return new BalanceDTO($userId, $balance->amount);
    }

    public function createBalance(int $userId, float $amount = 0.0): Balance
    {
        return Balance::create([
            'user_id' => $userId,
            'amount' => $amount,
        ]);
    }

    public function updateBalance(int $userId, float $amount): void
    {
        $user = User::findOrFail($userId);
        $balance = $user->ensureBalance();
        $balance->updateAmount($amount);
    }

    public function addToBalance(int $userId, float $amount): void
    {
        $user = User::findOrFail($userId);
        $balance = $user->ensureBalance();
        $balance->addAmount($amount);
    }

    public function subtractFromBalance(int $userId, float $amount): void
    {
        $user = User::findOrFail($userId);
        $balance = $user->ensureBalance();

        if ($balance->amount < $amount) {
            throw new \Exception('Insufficient balance');
        }

        $balance->subtractAmount($amount);
    }
}
