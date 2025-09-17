<?php

namespace App\Http\Controllers;

use App\DTO\OperationCreateDTO;
use App\Models\Operation;
use App\Services\OperationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OperationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Operation::where('user_id', Auth::id());
        if ($request->has('description')) {
            $query->where('description', 'like', '%'.$request->input('description').'%');
        }
        $operations = $query->orderByDesc('created_at')->get();

        return response()->json($operations);
    }

    public function store(Request $request, OperationService $service)
    {
        $data = $request->validate([
            'type' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
        ]);
        $dto = new OperationCreateDTO(Auth::id(), $data['type'], $data['amount'], $data['description'] ?? '');
        $operationDTO = $service->create($dto);

        return response()->json($operationDTO);
    }
}
