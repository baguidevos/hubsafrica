<?php

namespace App\Notifications;

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
        return $this->url;
    }

    public function toMail($notifiable): MailMessage
    {
        $expireMinutes = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe — '.config('hubafrica.name', 'Hub Africa'))
            ->greeting('Bonjour '.($notifiable->name ?? '').',')
            ->line('Vous recevez cet e-mail car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.')
            ->action('Réinitialiser mon mot de passe', $this->resetUrl($notifiable))
            ->line("Ce lien de réinitialisation expirera dans {$expireMinutes} minutes.")
            ->line("Si vous n'avez pas demandé de réinitialisation de mot de passe, aucune action supplémentaire n'est requise.");
    }
}
