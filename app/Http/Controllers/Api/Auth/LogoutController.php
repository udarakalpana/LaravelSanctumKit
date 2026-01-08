<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\Contracts\TokenServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    use ApiResponse;

    public function __construct(private TokenServiceInterface $tokenService)
    {
    }

    public function logout(Request $request)
    {
        $this->tokenService->revokeCurrentToken($request->user());

        return $this->successResponse(
            null,
            'Logged out successfully'
        );
    }
}
