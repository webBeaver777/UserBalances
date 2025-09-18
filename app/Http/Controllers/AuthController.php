<?php

namespace App\Http\Controllers;

use App\DTO\UserCreateDTO;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function register(RegisterUserRequest $registerUserRequest): JsonResponse
    {
        $data = $registerUserRequest->validated();
        $userCreateDTO = new UserCreateDTO($data['name'], $data['email'], $data['password']);
        $userDTO = $this->userService->register($userCreateDTO);

        return response()->json($userDTO);
    }

    public function login(LoginUserRequest $loginUserRequest): JsonResponse
    {
        $data = $loginUserRequest->validated();
        $result = $this->userService->login($data['email'], $data['password']);
        if ($result['error']) {
            return response()->json(['message' => $result['message']], $result['status']);
        }

        return response()->json(['message' => $result['message'], 'token' => $result['token']]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->userService->logout($user);

        return response()->json(['message' => 'Выход выполнен']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $userInfo = $this->userService->me($user);

        return response()->json($userInfo);
    }
}
