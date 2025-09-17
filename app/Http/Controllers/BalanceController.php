<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Services\BalanceService;
use App\Services\OperationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BalanceController extends Controller
{
    public function show(BalanceService $balanceService, OperationService $operationService): JsonResponse
    {
        $userId = Auth::id();
        $balanceDTO = $balanceService->getBalance($userId);
        $operations = Operation::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return response()->json([
            'balance' => $balanceDTO,
            'operations' => $operations,
        ]);
    }
}
