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

    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();
        if (! $user || ! \Hash::check($password, $user->password)) {
            return ['error' => true, 'message' => 'Неверные данные', 'status' => 401];
        }
        $token = $user->createToken('api-token')->plainTextToken;

        return ['error' => false, 'message' => 'Успешно', 'token' => $token];
    }

    public function logout($user): void
    {
        $token = $user ? $user->currentAccessToken() : null;
        if ($token) {
            $token->delete();
        }
    }

    public function me($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
}
