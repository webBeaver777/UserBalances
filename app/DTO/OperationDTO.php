<?php

namespace App\DTO;

class OperationDTO
{
    public function __construct(public int $id, public int $userId, public string $type, public float $amount, public string $description, public $createdAt) {}
}
