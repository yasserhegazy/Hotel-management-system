<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $token
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Build a simple reset URL. Adjust if your frontend expects a different path.
        $resetUrl = config('app.url').'/password/reset/'.$this->token
            .'?email='.urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject(__('Reset your password'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->first_name ?? $notifiable->name ?? '']))
            ->line(__('You are receiving this email because we received a password reset request for your account.'))
            ->action(__('Reset Password'), $resetUrl)
            ->line(__('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60)]))
            ->line(__('If you did not request a password reset, no further action is required.'))
            ->salutation(__('Regards, :app', ['app' => config('app.name')]));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'token' => $this->token,
        ];
    }
}
