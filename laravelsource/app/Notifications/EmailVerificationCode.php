<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationCode extends Notification
{
    use Queueable;

    public function __construct(
        public string $code
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Email Verification Code - Deadzone Revive')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Your email verification code is:')
            ->line('**'.$this->code.'**')
            ->line('This code will expire in 15 minutes.')
            ->line('Enter this code on the verification page to verify your email address.')
            ->line('If you did not create an account, no further action is required.');
    }
}
