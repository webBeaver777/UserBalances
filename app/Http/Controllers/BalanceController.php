<?php

namespace App\Http\Controllers;

use App\Services\BalanceService;
use App\Services\OperationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BalanceController extends Controller
{
    public function __construct(private readonly BalanceService $balanceService, private readonly OperationService $operationService) {}

    public function show(): JsonResponse
    {
        $userId = Auth::id();
        $balanceDTO = $this->balanceService->getBalance($userId);
        $operations = $this->operationService->getRecentOperations($userId, 5);

        return response()->json([
            'balance' => $balanceDTO,
            'operations' => $operations,
        ]);
    }
}
