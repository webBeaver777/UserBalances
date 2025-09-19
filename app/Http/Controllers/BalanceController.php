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
        $user = Auth::user();
        $balance = $user->balance?->amount ?? 0;
        return response()->json([
            'balance' => $balance,
        ]);
    }
}
