<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\Contracts\AuthServiceInterface;
use App\Services\Auth\Contracts\TokenServiceInterface;
use App\Traits\ApiResponse;

class RegisterController extends Controller
{
    use ApiResponse;

    public function __construct(
        private AuthServiceInterface $authService,
        private TokenServiceInterface $tokenService,
    ) {
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());
        $token = $this->tokenService->createToken($user, 'auth-token');

        return $this->successResponse(
            [
                'user' => new UserResource($user),
                'token' => $token,
            ],
            'Registration successful',
            201
        );
    }
}
