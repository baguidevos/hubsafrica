<?php

namespace App\Notifications;

use Filament\Facades\Filament;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class FilamentResetPassword extends BaseResetPassword
{
    /**
     * URL de réinitialisation générée par Filament.
     */
    public string $url = '';

    public function __construct(string $token)
    {
        parent::__construct($token);
    }

    /**
     * Ne pas implémenter ShouldQueue : envoi synchrone immédiat
     * pour garantir la réception même si le worker de queue n'est pas actif en production.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    protected function resetUrl($notifiable): string
    {
        if (filled($this->url)) {
            return $this->url;
        }

        try {
            return Filament::getResetPasswordUrl($this->token, $notifiable);
        } catch (\Throwable) {
            return url('/admin/password-reset/reset?token='.$this->token.'&email='.urlencode($notifiable->getEmailForPasswordReset() ?? ''));
        }
    }

    public function toMail($notifiable): MailMessage
    {
        $expireMinutes = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);
        $url = $this->resetUrl($notifiable);
        $name = $notifiable->name ?? 'Utilisateur';

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe — '.config('hubafrica.name', 'Hub Africa'))
            ->view('emails.auth.reset-password', [
                'user' => $notifiable,
                'name' => $name,
                'resetUrl' => $url,
                'expireMinutes' => $expireMinutes,
            ])
            ->action('Réinitialiser mon mot de passe', $url);
    }
}
