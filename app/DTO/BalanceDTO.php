<?php

namespace App\DTO;

class BalanceDTO
{
    public int $userId;

    public float $amount;

    public function __construct(int $userId, float $amount)
    {
        $this->userId = $userId;
        $this->amount = $amount;
    }
}
