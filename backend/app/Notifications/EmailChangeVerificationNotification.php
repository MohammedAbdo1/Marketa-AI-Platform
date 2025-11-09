<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailChangeVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected string $code)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Confirm your email change request'))
            ->greeting(__('Hi :name,', ['name' => $notifiable->name]))
            ->line(__('We received a request to change the email address on your Marketa account.'))
            ->line(__('Use the verification code below to continue:'))
            ->line('')
            ->line("**{$this->code}**")
            ->line('')
            ->line(__('This code will expire in 10 minutes. If you did not request this change, you can safely ignore this email.'));
    }
}

