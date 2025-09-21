<?php

use App\Http\Controllers\Api\OperationApiController;
use App\Http\Controllers\Api\SseController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function (): void {
    // Используем только OperationApiController для всех API операций
    Route::get('/balance', [OperationApiController::class, 'balance']);
    Route::get('/operations', [OperationApiController::class, 'index']);
    Route::post('/operations', [OperationApiController::class, 'store']);
});

Route::get('/sse/balance', [SseController::class, 'balance']);
Route::get('/sse/operations', [SseController::class, 'operations']);
