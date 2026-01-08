<?php

namespace App\Services\Auth\Contracts;

use App\Models\User;

interface AuthServiceInterface
{
    public function register(array $data): User;

    public function authenticate(array $credentials): User;

    public function revokeToken(User $user): bool;
}
