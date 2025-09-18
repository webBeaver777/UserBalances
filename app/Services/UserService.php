<?php

namespace App\Services;

use App\DTO\UserCreateDTO;
use App\DTO\UserDTO;
use App\Models\Balance;
use App\Models\User;

class UserService
{
    public function register(UserCreateDTO $userCreateDTO): UserDTO
    {
        $user = User::create([
            'name' => $userCreateDTO->name,
            'email' => $userCreateDTO->email,
            'password' => bcrypt($userCreateDTO->password),
        ]);
        // Создаём баланс пользователя
        Balance::create([
            'user_id' => $user->id,
            'amount' => 0.0,
        ]);

        return new UserDTO($user->id, $user->name, $user->email);
    }
}
