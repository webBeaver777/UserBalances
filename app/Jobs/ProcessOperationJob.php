<?php

namespace App\Jobs;

use App\Models\Operation;
use App\Services\OperationService;
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
        try {
            $operation = Operation::findOrFail($this->operationId);
            $operationService->processOperation($operation);

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

            throw $e;
        }
    }

    public function failed(\Throwable $throwable): void
    {
        Log::error('ProcessOperationJob failed', [
            'operation_id' => $this->operationId,
            'error' => $throwable->getMessage(),
        ]);

        // Помечаем операцию как неудачную
        $operation = Operation::find($this->operationId);
        if ($operation && $operation->isPending()) {
            $operation->markAsFailed();
        }
    }
}
