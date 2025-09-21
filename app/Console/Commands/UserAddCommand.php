<?php

namespace App\Console\Commands;

use App\Services\UserService;
use Illuminate\Console\Command;

class UserAddCommand extends Command
{
    protected $signature = 'user:add {name} {email} {password}';

    protected $description = 'Создать нового пользователя';

    public function __construct(private readonly UserService $userService)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->argument('password');

        // Проверяем, не существует ли уже пользователь с таким email
        if ($this->userService->findByEmail($email) instanceof \App\Models\User) {
            $this->error("Пользователь с email {$email} уже существует");

            return;
        }

        try {
            $user = $this->userService->createUser($name, $email, $password);

            $this->info("Пользователь создан успешно: {$user->name} ({$user->email})");
            $this->info("ID пользователя: {$user->id}");
            $this->info("Начальный баланс: {$user->getBalanceAmount()} ₽");

        } catch (\Exception $e) {
            $this->error('Ошибка при создании пользователя: '.$e->getMessage());
        }
    }
}
