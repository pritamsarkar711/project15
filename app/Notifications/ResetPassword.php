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
     *
     * IMPORTANT: emails are read OUTSIDE the site, so the link must be fully
     * absolute (https://huvanti.com/reset-password/...). The app runs a
     * root-relative UrlGenerator by default (see RelativeAssetUrlGenerator),
     * which would emit "/reset-password/..." — a link that is dead in every
     * mail client. We therefore prepend the site origin explicitly, with a
     * sane fallback chain: request host -> configured APP_URL.
     */
    protected function resetUrl($notifiable): string
    {
        $path = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false);

        // Already absolute (in case the relative generator is removed later).
        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }

        $base = '';
        try {
            $base = rtrim(request()->getSchemeAndHttpHost(), '/');
        } catch (\Throwable $e) {
            $base = '';
        }
        if ($base === '' || str_contains($base, 'localhost')) {
            $configured = rtrim((string) config('app.url', ''), '/');
            if ($configured !== '' && !str_contains($configured, 'localhost')) {
                $base = $configured;
            }
        }

        return $base . $path;
    }
}
