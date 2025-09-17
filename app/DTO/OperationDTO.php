<?php

namespace App\DTO;

class OperationDTO
{
    public int $id;

    public int $userId;

    public string $type;

    public float $amount;

    public string $description;

    public $createdAt;

    public function __construct(int $id, int $userId, string $type, float $amount, string $description, $createdAt)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->type = $type;
        $this->amount = $amount;
        $this->description = $description;
        $this->createdAt = $createdAt;
    }
}
