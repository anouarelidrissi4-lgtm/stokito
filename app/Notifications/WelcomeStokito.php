<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent once after a self-service sign-up (2026-11-23). Synchronous on purpose: there is no queue worker. */
class WelcomeStokito extends Notification
{
    public function __construct(public string $company) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Bienvenue sur Stokito'))
            ->greeting(__('Bonjour :name,', ['name' => $notifiable->first_name ?: $notifiable->name]))
            ->line(__('L’espace de :company est prêt. Pour bien démarrer, trois étapes suffisent :', ['company' => $this->company]))
            ->line(__('1. Complétez Ma Société : logo, adresse et identifiants apparaissent sur vos PDF.'))
            ->line(__('2. Ajoutez vos produits, un par un ou en important votre fichier Excel.'))
            ->line(__('3. Faites votre premier devis, puis transformez-le en bon de livraison et en facture.'))
            ->action(__('Ouvrir mon espace'), route('dashboard'))
            ->line(__('Le guide d’utilisation explique chaque écran : :url', ['url' => url('/docs/guide-stockpilot-fr.pdf')]))
            ->line(__('Une question ? Répondez simplement à cet email ou écrivez-nous sur WhatsApp au +212 676 035 777.'))
            ->salutation(__('L’équipe Stokito'));
    }
}
