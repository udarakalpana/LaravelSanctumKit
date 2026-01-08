<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Password;

class PasswordResetService
{
    public function requestReset(string $email): bool
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return false;
        }

        $token = Password::createToken($user);
        $user->notify(new ResetPasswordNotification($token));

        return true;
    }

    public function resetPassword(string $email, string $token, string $password): bool
    {
        $status = Password::reset(
            ['email' => $email, 'password' => $password, 'password_confirmation' => $password, 'token' => $token],
            function (User $user, string $password) {
                $user->forceFill(['password' => $password])->save();
            }
        );

        return $status === Password::PASSWORD_RESET;
    }
}
