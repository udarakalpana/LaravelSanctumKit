<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Notifications\VerifyEmailNotification;

class SendEmailVerificationNotification
{
    public function handle(UserRegistered $event): void
    {
        $event->user->notify(new VerifyEmailNotification());
    }
}
