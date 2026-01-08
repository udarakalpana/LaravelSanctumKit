<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\Auth\PasswordResetService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    use ApiResponse;

    public function __construct(private PasswordResetService $passwordResetService)
    {
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        if (!$this->passwordResetService->requestReset($request->validated()['email'])) {
            return $this->errorResponse(
                'Failed to send reset link',
                400
            );
        }

        return $this->successResponse(
            null,
            'Password reset link sent to your email'
        );
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        if (!$this->passwordResetService->resetPassword(
            $request->validated()['email'],
            $request->validated()['token'],
            $request->validated()['password']
        )) {
            return $this->errorResponse(
                'Invalid or expired reset token',
                400
            );
        }

        return $this->successResponse(
            null,
            'Password reset successfully'
        );
    }
}
