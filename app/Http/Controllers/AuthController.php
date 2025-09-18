<?php

namespace App\Http\Controllers;

use App\DTO\UserCreateDTO;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterUserRequest $registerUserRequest, UserService $userService): JsonResponse
    {
        $data = $registerUserRequest->validated();
        $userCreateDTO = new UserCreateDTO($data['name'], $data['email'], $data['password']);
        $userDTO = $userService->register($userCreateDTO);

        return response()->json($userDTO);
    }

    public function login(LoginUserRequest $loginUserRequest): JsonResponse
    {
        $data = $loginUserRequest->validated();
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
