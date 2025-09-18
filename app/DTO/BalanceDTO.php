<?php

namespace App\DTO;

class BalanceDTO
{
    public function __construct(public int $userId, public float $amount) {}
}
