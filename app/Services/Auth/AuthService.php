<?php

namespace App\Services\Auth;

use App\Events\UserRegistered;
use App\Models\User;
use App\Services\Auth\Contracts\AuthServiceInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceInterface
{
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        UserRegistered::dispatch($user);

        return $user;
    }

    public function authenticate(array $credentials): User
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new AuthenticationException('The provided credentials are incorrect.');
        }

        return $user;
    }

    public function revokeToken(User $user): bool
    {
        return (bool) $user->currentAccessToken()?->delete();
    }
}
