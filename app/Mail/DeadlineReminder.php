<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;

class DeadlineReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $deadlines;
    public $user;
    public $step;
    public $deadline;

    /**
     * Passiamo la lista delle scadenze e l'utente
     */
    public function __construct(Collection $deadlines, User $user)
    {
        $this->deadlines = $deadlines;
        $this->user = $user;
        $this->step = $deadlines->first()->step ?? 'default';
        $this->deadline = $deadlines->first() ?? null;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Avviso Scadenze Imminenti - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.deadline-reminder',
        );
    }
}