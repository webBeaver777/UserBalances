<?php

namespace App\Console\Commands;

use App\DTO\OperationCreateDTO;
use App\Jobs\ProcessOperationJob;
use App\Models\User;
use Illuminate\Console\Command;

class BalanceOperationCommand extends Command
{
    protected $signature = 'balance:operation {email} {type} {amount} {description}';

    protected $description = 'Провести операцию с балансом пользователя';

    public function handle(): void
    {
        $user = User::where('email', $this->argument('email'))->first();
        if (! $user) {
            $this->error('Пользователь не найден');

            return;
        }
        $operationCreateDTO = new OperationCreateDTO(
            $user->id,
            $this->argument('type'),
            (float) $this->argument('amount'),
            $this->argument('description')
        );
        try {
            ProcessOperationJob::dispatch($operationCreateDTO);
            $this->info('Операция поставлена в очередь: '.$operationCreateDTO->type.' '.$operationCreateDTO->amount);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
