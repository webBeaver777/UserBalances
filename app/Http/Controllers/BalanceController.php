<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BalanceController extends Controller
{
    public function __construct() {}

    public function show(): JsonResponse
    {
        $user = Auth::user();
        $balance = $user->balance->amount ?? 0;

        return response()->json([
            'balance' => $balance,
        ]);
    }
}
