<?php

namespace App\Http\Controllers;

use App\DTO\OperationCreateDTO;
use App\Http\Requests\StoreOperationRequest;
use App\Services\OperationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OperationController extends Controller
{
    public function __construct(private readonly OperationService $operationService) {}

    public function index(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $description = $request->input('description');
        $operations = $this->operationService->searchOperations($userId, $description);

        return response()->json($operations);
    }

    public function store(StoreOperationRequest $storeOperationRequest): JsonResponse
    {
        $data = $storeOperationRequest->validated();
        $operationCreateDTO = new OperationCreateDTO(Auth::id(), $data['type'], $data['amount'], $data['description'] ?? '');

        if ($storeOperationRequest->boolean('async')) {
            $this->operationService->create($operationCreateDTO);

            return response()->json(['status' => 'queued']);
        }

        $operation = $this->operationService->store($operationCreateDTO);

        return response()->json([
            'id' => $operation->id,
            'userId' => $operation->user_id,
            'type' => $operation->type,
            'amount' => $operation->amount,
            'description' => $operation->description,
            'createdAt' => $operation->created_at,
        ]);
    }
}
