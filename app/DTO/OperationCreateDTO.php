<?php

namespace App\DTO;

class OperationCreateDTO
{
    public function __construct(public int $userId, public string $type, public float $amount, public string $description) {}
}
