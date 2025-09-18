<?php

namespace App\Http\Controllers;

use App\DTO\UserCreateDTO;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request, UserService $service): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);
        $dto = new UserCreateDTO($data['name'], $data['email'], $data['password']);
        $userDTO = $service->register($dto);

        return response()->json($userDTO);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Неверные данные'], 401);
        }
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['message' => 'Успешно', 'token' => $token]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $user ? $user->currentAccessToken() : null;
        if ($token) {
            $token->delete();
        }

        return response()->json(['message' => 'Выход выполнен']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(Auth::user());
    }
}
