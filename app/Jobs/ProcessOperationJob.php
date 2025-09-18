<?php

namespace App\Jobs;

use App\DTO\OperationCreateDTO;
use App\Services\OperationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessOperationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public OperationCreateDTO $dto;

    public function __construct(OperationCreateDTO $dto)
    {
        $this->dto = $dto;
    }

    public function handle(OperationService $service): void
    {
        $service->store($this->dto);
    }
}
