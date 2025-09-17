<?php

namespace App\Services;

use App\DTO\UserCreateDTO;
use App\DTO\UserDTO;
use App\Models\Balance;
use App\Models\User;

class UserService
{
    public function register(UserCreateDTO $dto): UserDTO
    {
        $user = User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => bcrypt($dto->password),
        ]);
        // Создаём баланс пользователя
        Balance::create([
            'user_id' => $user->id,
            'amount' => 0.0,
        ]);

        return new UserDTO($user->id, $user->name, $user->email);
    }
}
