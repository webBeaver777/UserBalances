<?php

namespace App\DTO;

readonly class OperationCreateDTO
{
    public function __construct(
        public int $userId,
        public string $type,
        public float $amount,
        public string $description = ''
    ) {}

    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
            'type' => $this->type,
            'amount' => $this->amount,
            'description' => $this->description,
        ];
    }
}
