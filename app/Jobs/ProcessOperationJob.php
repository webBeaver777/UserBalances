<?php

namespace App\Jobs;

use App\Models\Balance;
use App\Models\Operation;
use App\Services\OperationService;
use App\Services\RealtimeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOperationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly int $operationId) {}

    public function handle(OperationService $operationService): void
    {
        $realtimeService = app(RealtimeService::class);

        try {
            $operation = Operation::findOrFail($this->operationId);
            $operationService->processOperation($operation);

            // Обновляем данные для клиента через SSE
            $userId = $operation->user_id;
            // читаем баланс напрямую из БД после коммита
            $balanceAmount = (float) (Balance::where('user_id', $userId)->value('amount') ?? 0);

            $realtimeService->publishBalanceUpdated($userId, $balanceAmount);
            $realtimeService->publishOperationsUpdated($userId, 5);

            Log::info('Operation processed successfully', [
                'operation_id' => $this->operationId,
                'type' => $operation->type,
                'amount' => $operation->amount,
                'status' => $operation->status,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process operation in job', [
                'operation_id' => $this->operationId,
                'error' => $e->getMessage(),
            ]);

            // Помечаем операцию как неудачную, если она еще не была обработана
            $operation = Operation::find($this->operationId);
            if ($operation && $operation->isPending()) {
                $operation->markAsFailed();
            }

            // Сообщаем клиентам, что список операций обновился
            if ($operation) {
                $realtimeService->publishOperationsUpdated($operation->user_id, 5);
            }

            throw $e;
        }
    }

    public function failed(\Throwable $throwable): void
    {
        Log::error('ProcessOperationJob failed', [
            'operation_id' => $this->operationId,
            'exception' => $throwable->getMessage(),
        ]);
    }
}
