<?php

namespace App\DTO;

readonly class OperationDTO
{
    public function __construct(
        public int $id,
        public int $userId,
        public string $type,
        public float $amount,
        public string $description,
        public string $status,
        public string $createdAt
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'type' => $this->type,
            'amount' => $this->amount,
            'description' => $this->description,
            'status' => $this->status,
            'createdAt' => $this->createdAt,
        ];
    }
}
