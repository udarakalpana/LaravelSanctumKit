<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Auth\Contracts\TokenServiceInterface;

class TokenService implements TokenServiceInterface
{
    public const ABILITY_ALL = '*';
    public const ABILITY_USER_READ = 'user:read';
    public const ABILITY_USER_WRITE = 'user:write';
    public const ABILITY_ADMIN = 'admin';

    public function createToken(User $user, string $name, array $abilities = ['*']): string
    {
        $token = $user->createToken($name, $abilities);
        return $token->plainTextToken;
    }

    public function revokeCurrentToken(User $user): bool
    {
        $token = $user->currentAccessToken();
        if (!$token) {
            return false;
        }
        return (bool) $token->delete();
    }

    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }
}
