<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;

class ResetPassword extends ResetPasswordNotification
{
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject(__('messages.reset_password.email.subject'))
            ->line(__('messages.reset_password.email.intro'))
            ->action(__('messages.reset_password.email.action'), $url)
            ->line(__('messages.reset_password.email.expiration', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(__('messages.reset_password.email.no_action'));
    }
}
