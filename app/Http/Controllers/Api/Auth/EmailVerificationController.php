<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    use ApiResponse;

    public function verify(Request $request)
    {
        $user = User::findOrFail($request->route('id'));

        if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return $this->errorResponse(
                'Invalid verification link',
                403
            );
        }

        if ($user->hasVerifiedEmail()) {
            return $this->successResponse(
                null,
                'Email already verified'
            );
        }

        if ($user->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($user));
        }

        return $this->successResponse(
            null,
            'Email verified successfully'
        );
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->errorResponse(
                'Email already verified',
                400
            );
        }

        $request->user()->sendEmailVerificationNotification();

        return $this->successResponse(
            null,
            'Verification email sent'
        );
    }
}
