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
        $dto = new OperationCreateDTO(
            $user->id,
            $this->argument('type'),
            (float) $this->argument('amount'),
            $this->argument('description')
        );
        try {
            ProcessOperationJob::dispatch($dto);
            $this->info('Операция поставлена в очередь: '.$dto->type.' '.$dto->amount);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
