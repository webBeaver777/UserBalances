<?php

namespace App\DTO;

class OperationCreateDTO
{
    public int $userId;

    public string $type;

    public float $amount;

    public string $description;

    public function __construct(int $userId, string $type, float $amount, string $description)
    {
        $this->userId = $userId;
        $this->type = $type;
        $this->amount = $amount;
        $this->description = $description;
    }
}
