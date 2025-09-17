<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\OperationController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/balance', [BalanceController::class, 'show']);
    Route::get('/operations', [OperationController::class, 'index']);
    Route::post('/operations', [OperationController::class, 'store']);
});
