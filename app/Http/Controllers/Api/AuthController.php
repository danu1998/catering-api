<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->validated()
        );

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => $result,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->authService->me(
                $request->user()
            ),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }
}