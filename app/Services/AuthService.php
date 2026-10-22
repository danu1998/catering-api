<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    public function login(array $data): array
    {
        $user = User::with('role')
            ->where('email', $data['email'])
            ->first();

        if (
            !$user ||
            !Hash::check($data['password'], $user->password)
        ) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password tidak valid.'],
            ]);
        }

        if (!$user->role || !$user->role->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Akun belum memiliki role aktif.'],
            ]);
        }

        $token = $user->createToken('catering-web')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
            'role' => $user->role->name,
        ];
    }

    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }

    public function me(User $user): User
    {
        return $user->load('role.permissions');
    }
}
