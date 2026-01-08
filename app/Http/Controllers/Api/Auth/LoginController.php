<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\Contracts\AuthServiceInterface;
use App\Services\Auth\Contracts\TokenServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Auth\AuthenticationException;

class LoginController extends Controller
{
    use ApiResponse;

    public function __construct(
        private AuthServiceInterface $authService,
        private TokenServiceInterface $tokenService,
    ) {
    }

    public function login(LoginRequest $request)
    {
        try {
            $user = $this->authService->authenticate($request->validated());
        } catch (AuthenticationException $e) {
            return $this->errorResponse(
                $e->getMessage(),
                401
            );
        }

        $deviceName = $request->validated()['device_name'] ?? 'auth-token';
        $token = $this->tokenService->createToken($user, $deviceName);

        return $this->successResponse(
            [
                'user' => new UserResource($user),
                'token' => $token,
            ],
            'Login successful'
        );
    }
}
