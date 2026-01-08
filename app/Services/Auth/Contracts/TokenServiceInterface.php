<?php

namespace App\Services\Auth\Contracts;

use App\Models\User;

interface TokenServiceInterface
{
    public function createToken(User $user, string $name, array $abilities = ['*']): string;

    public function revokeCurrentToken(User $user): bool;

    public function revokeAllTokens(User $user): void;
}
