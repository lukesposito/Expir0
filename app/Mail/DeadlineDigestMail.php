<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeadlineDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $notifiche;

    public function __construct($notifiche)
    {
        $this->notifiche = $notifiche;
    }

    public function envelope(): Envelope
    {
        // Se ci sono scadenze urgenti, l'oggetto fa capire che è importante!
        $urgenti = count($this->notifiche['urgente']);
        
        if ($urgenti > 0) {
            $subject = "🚨 URGENTE: Hai $urgenti scadenze imminenti!";
        } else {
            $subject = "📅 Il tuo Riepilogo Scadenze e Promemoria";
        }

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.deadline-digest',
            with: [
                'notifiche' => $this->notifiche, 
            ]
        );
    }
}