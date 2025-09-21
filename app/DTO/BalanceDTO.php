<?php

namespace App\DTO;

readonly class BalanceDTO
{
    public function __construct(
        public int $userId,
        public float $amount
    ) {}

    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
            'amount' => $this->amount,
        ];
    }
}
