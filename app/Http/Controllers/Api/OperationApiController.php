<?php

namespace App\Http\Controllers\Api;

use App\DTO\OperationCreateDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchOperationsRequest;
use App\Http\Requests\StoreOperationRequest;
use App\Services\BalanceService;
use App\Services\OperationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OperationApiController extends Controller
{
    public function __construct(
        private readonly BalanceService $balanceService,
        private readonly OperationService $operationService
    ) {}

    public function index(SearchOperationsRequest $searchOperationsRequest): JsonResponse
    {
        $user = Auth::user();

        $validated = $searchOperationsRequest->validated();

        $operations = $this->operationService->getOperations(
            $user->id,
            $validated['limit'] ?? null,
            $validated['search'] ?? null,
            $validated['page'] ?? 1
        );

        return response()->json([
            'success' => true,
            'data' => $operations['data'],
            'pagination' => $operations['pagination'],
        ]);
    }

    public function store(StoreOperationRequest $storeOperationRequest): JsonResponse
    {
        $user = Auth::user();

        // Быстрая валидация недостатка средств для списания (user-friendly)
        $type = $storeOperationRequest->validated('type');
        $amount = (float) $storeOperationRequest->validated('amount');
        if ($type === 'withdrawal') {
            $balance = $this->balanceService->getBalance($user->id);
            if ($amount > $balance->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Недостаточно средств для списания',
                ], 422);
            }
        }

        try {
            $operationCreateDTO = new OperationCreateDTO(
                $user->id,
                $type,
                $amount,
                $storeOperationRequest->validated('description')
            );

            $operation = $this->operationService->store($operationCreateDTO); // отправится в очередь

            return response()->json([
                'success' => true,
                'message' => 'Операция поставлена в очередь на обработку',
                'data' => $operation,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function balance(): JsonResponse
    {
        $user = Auth::user();
        $balanceDTO = $this->balanceService->getBalance($user->id);

        return response()->json([
            'success' => true,
            'balance' => $balanceDTO->amount,
        ]);
    }
}
