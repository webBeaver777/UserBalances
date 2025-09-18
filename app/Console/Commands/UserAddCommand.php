<?php

namespace App\Console\Commands;

use App\DTO\UserCreateDTO;
use App\Services\UserService;
use Illuminate\Console\Command;

class UserAddCommand extends Command
{
    protected $signature = 'user:add {name} {email} {password}';

    protected $description = 'Добавить пользователя';

    public function handle(UserService $userService): void
    {
        $userCreateDTO = new UserCreateDTO(
            $this->argument('name'),
            $this->argument('email'),
            $this->argument('password')
        );
        try {
            $userDTO = $userService->register($userCreateDTO);
            $this->info('Пользователь добавлен: '.$userDTO->email);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
