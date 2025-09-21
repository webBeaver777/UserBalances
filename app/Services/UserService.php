<?php

namespace App\Services;

use App\DTO\UserCreateDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function createUser(string $name, string $email, string $password): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        // Создаем баланс для нового пользователя
        $user->ensureBalance();

        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function getUserWithBalance(int $userId): User
    {
        return User::with('balance')->findOrFail($userId);
    }
}
