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

        // Получаем параметры из запроса
        $search = $request->input('search');
        $page = $request->input('page');
        $perPage = (int) $request->input('per_page', 25);
        $sortDesc = $request->boolean('sort_desc', true);

        // Если есть лимит (для Dashboard), используем его вместо пагинации
        $limit = $request->input('limit');
        if ($limit) {
            $operations = $this->operationService->getOperationsWithLimit($userId, $search, $sortDesc, (int) $limit);

            return response()->json($operations);
        }

        // Если указана страница, используем пагинацию
        if ($page) {
            $result = $this->operationService->getOperationsWithPagination($userId, $search, $sortDesc, (int) $page, $perPage);

            return response()->json($result);
        }

        // Иначе возвращаем все операции как простой массив
        $operations = $this->operationService->searchOperations($userId, $search);
        if (! $sortDesc) {
            $operations = $operations->sortBy('created_at');
        }

        return response()->json($operations->values()->toArray());
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
