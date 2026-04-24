<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class DeadlinePushNotification extends Notification
{
    use Queueable;

    public $itemData;

    // Riceviamo i dati della scadenza dal comando Cron
    public function __construct($itemData)
    {
        $this->itemData = $itemData;
    }

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        $giorni = $this->itemData['giorni'];
        
        // Testo dinamico in base ai giorni
        if ($giorni === 0) {
            $testoGiorni = "Scade OGGI!";
        } elseif ($giorni < 0) {
            $testoGiorni = "Scaduta da " . abs($giorni) . " giorni. URGENZA!";
        } else {
            $testoGiorni = "Scade tra {$giorni} giorni.";
        }

        return (new WebPushMessage)
            ->title('Scadenza: ' . $this->itemData['tipo'])
            ->icon('/images/logo.png')
            ->body($this->itemData['soggetto'] . ' - ' . $testoGiorni)
            ->action('Vedi Dettagli', 'open_app');
    }
}