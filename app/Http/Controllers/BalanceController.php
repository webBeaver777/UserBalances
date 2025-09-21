<?php

namespace App\Http\Controllers;

use App\Services\OperationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BalanceController extends Controller
{
    public function __construct(private readonly OperationService $operationService) {}

    public function show(): JsonResponse
    {
        $user = Auth::user();
        $balance = $user->balance;
        $operations = $this->operationService->getRecentOperations($user->id);

        return response()->json([
            'balance' => [
                'userId' => $user->id,
                'amount' => $balance ? $balance->amount : 0,
            ],
            'operations' => $operations,
        ]);
    }
}
