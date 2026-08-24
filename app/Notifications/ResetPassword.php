<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

/**
 * Frontend user password-reset email.
 *
 * Laravel's default ResetPassword notification builds a URL pointing at
 * /reset-password/{token} — which matches our frontend route. We only need
 * to override the email copy so the message matches Huvanti's voice.
 */
class ResetPassword extends BaseResetPassword
{
    /**
     * Build the mail message for the reset link.
     *
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);

        $intro = $this->token
            ? 'You\'re receiving this email because we received a password reset request for your Huvanti account.'
            : 'You\'re receiving this email because we received a password reset request for your Huvanti account.';

        return (new MailMessage())
            ->subject('Reset your Huvanti password')
            ->line($intro)
            ->action('Reset Password', $url)
            ->line('If you didn\'t request a password reset, no further action is required — your account is safe.')
            ->line('This reset link expires in 60 minutes.');
    }

    /**
     * Get the reset URL for the given notifiable.
     * Uses route('password.reset') which is /reset-password/{token}.
     */
    protected function resetUrl($notifiable): string
    {
        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}
