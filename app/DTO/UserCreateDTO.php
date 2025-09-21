<?php

namespace App\DTO;

readonly class UserCreateDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
